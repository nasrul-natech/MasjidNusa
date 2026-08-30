@once
@push('scripts')
<script>
window.quranReaderCore = function (config) {
    const playlist = config.versePlaylist || [];
    const pageMetaMap = config.pageMetaMap || {};
    const khatamProgramId = config.khatamProgramId || null;
    const khatamSubmittedKey = khatamProgramId ? ('quran_khatam_submitted_' + khatamProgramId) : null;

    function getKhatamSubmittedPages() {
        if (!khatamSubmittedKey) return [];
        try {
            return JSON.parse(localStorage.getItem(khatamSubmittedKey) || '[]');
        } catch { return []; }
    }

    function saveKhatamSubmittedPages(pages) {
        if (!khatamSubmittedKey) return;
        localStorage.setItem(khatamSubmittedKey, JSON.stringify(pages));
    }

    function khatamSessionStorageKey() {
        return khatamProgramId ? ('quran_khatam_session_' + khatamProgramId) : null;
    }
    const playlistIndex = {};
    playlist.forEach((v, i) => {
        playlistIndex[v.surah_number + ':' + v.verse_number] = i;
    });

    const pageVersesMap = {};
    playlist.forEach(v => {
        const key = String(v.page_number);
        if (!pageVersesMap[key]) pageVersesMap[key] = [];
        pageVersesMap[key].push({ surah_number: v.surah_number, verse_number: v.verse_number });
    });

    function versesOnPage(pageNum) {
        return pageVersesMap[String(pageNum)] || pageVersesMap[pageNum] || [];
    }

    const bookmarkSet = new Set(config.bookmarkedKeys || []);

    function readStorageKey(surahNumber) {
        return 'quran_read_' + surahNumber;
    }

    function loadReadVerses(surahNumber) {
        try {
            return JSON.parse(localStorage.getItem(readStorageKey(surahNumber)) || '[]');
        } catch { return []; }
    }

    function saveReadVerses(surahNumber, verses) {
        localStorage.setItem(readStorageKey(surahNumber), JSON.stringify(verses));
    }

    return {
        versePlaylist: playlist,
        bookmarkSet,
        readVersesMap: {},
        readMushafPages: JSON.parse(localStorage.getItem('quran_read_pages') || '[]'),
        _readTick: 0,

        khatamAvailable: !!config.khatamProgressUrl,
        khatamEnabled: false,
        showKhatamSessionPrompt: false,
        khatamProgramTitle: config.khatamProgramTitle || '',
        khatamProgressPercent: Number(config.initialKhatamProgress ?? 0),
        khatamPagesRead: Number(config.initialKhatamPagesRead ?? 0),

        audio: null,
        currentSurah: 0,
        currentAyat: 0,
        currentAyatLabel: '',
        isPlaying: false,
        audioProgress: 0,

        initReadVerses() {
            const surahs = [...new Set(playlist.map(v => v.surah_number))];
            if (config.defaultSurahNumber && !surahs.includes(config.defaultSurahNumber)) {
                surahs.push(config.defaultSurahNumber);
            }
            surahs.forEach(sn => {
                this.readVersesMap[sn] = loadReadVerses(sn);
            });
            // Sinkronkan: halaman sudah dibaca → semua ayat di halaman ikut tercentang
            this.readMushafPages.forEach(p => this.markAllVersesOnPage(p, { silent: true }));
            // Sinkronkan: semua ayat tercentang → halaman ikut ditandai selesai
            [...new Set(playlist.map(v => v.page_number).filter(Boolean))].forEach(p => {
                if (this.isPageFullyReadByVerses(p) && !this.readMushafPages.includes(p)) {
                    this.readMushafPages.push(p);
                }
            });
            localStorage.setItem('quran_read_pages', JSON.stringify(this.readMushafPages));
            this.bumpReadUi();
        },

        bumpReadUi() {
            this._readTick++;
        },

        initKhatamSession() {
            if (!config.khatamProgressUrl || !khatamProgramId) return;

            const key = khatamSessionStorageKey();
            const choice = sessionStorage.getItem(key);

            if (choice === 'active') {
                this.khatamEnabled = true;
                this.showKhatamSessionPrompt = false;
            } else if (choice === 'off') {
                this.khatamEnabled = false;
                this.showKhatamSessionPrompt = false;
            } else {
                this.khatamEnabled = false;
                this.showKhatamSessionPrompt = true;
            }
        },

        enableKhatamSession() {
            const key = khatamSessionStorageKey();
            if (!key) return;
            sessionStorage.setItem(key, 'active');
            this.khatamEnabled = true;
            this.showKhatamSessionPrompt = false;
            if (typeof showToast === 'function') showToast('Progress khatam aktif untuk sesi ini', 'success');
        },

        disableKhatamSession() {
            const key = khatamSessionStorageKey();
            if (!key) return;
            sessionStorage.setItem(key, 'off');
            this.khatamEnabled = false;
            this.showKhatamSessionPrompt = false;
            if (typeof showToast === 'function') showToast('Mode baca rutin — khatam tidak dicatat', 'info');
        },

        toggleKhatamSession() {
            if (this.khatamEnabled) this.disableKhatamSession();
            else this.enableKhatamSession();
        },

        ayatKey(sn, vn) { return sn + ':' + vn; },
        isAyatPlaying(sn, vn) {
            return this.currentSurah === sn && this.currentAyat === vn && this.isPlaying;
        },

        isVerseRead(surahNumber, verseNumber) {
            void this._readTick;
            const item = playlist.find(v => v.surah_number === surahNumber && v.verse_number === verseNumber);
            if (item?.page_number && this.isPageRead(item.page_number)) {
                return true;
            }
            const verses = this.readVersesMap[surahNumber] || loadReadVerses(surahNumber);
            return verses.includes(verseNumber);
        },

        isPageFullyReadByVerses(pageNum) {
            const onPage = versesOnPage(pageNum);
            if (!onPage.length) return false;
            return onPage.every(v => {
                const list = this.readVersesMap[v.surah_number] || loadReadVerses(v.surah_number);
                return list.includes(v.verse_number);
            });
        },

        markAllVersesOnPage(pageNum, options = {}) {
            versesOnPage(pageNum).forEach(v => {
                if (!this.readVersesMap[v.surah_number]) {
                    this.readVersesMap[v.surah_number] = loadReadVerses(v.surah_number);
                }
                const verses = this.readVersesMap[v.surah_number];
                if (!verses.includes(v.verse_number)) verses.push(v.verse_number);
                saveReadVerses(v.surah_number, verses);
            });
            if (!options.silent) this.bumpReadUi();
        },

        unmarkAllVersesOnPage(pageNum) {
            versesOnPage(pageNum).forEach(v => {
                if (!this.readVersesMap[v.surah_number]) return;
                const verses = this.readVersesMap[v.surah_number];
                const idx = verses.indexOf(v.verse_number);
                if (idx >= 0) verses.splice(idx, 1);
                saveReadVerses(v.surah_number, verses);
            });
            this.bumpReadUi();
        },

        toggleVerseRead(surahNumber, verseNumber) {
            if (!this.readVersesMap[surahNumber]) {
                this.readVersesMap[surahNumber] = loadReadVerses(surahNumber);
            }
            const verses = this.readVersesMap[surahNumber];
            const idx = verses.indexOf(verseNumber);
            const item = playlist.find(v => v.surah_number === surahNumber && v.verse_number === verseNumber);
            const pageNum = item?.page_number;

            if (idx >= 0) {
                verses.splice(idx, 1);
                saveReadVerses(surahNumber, verses);
                if (pageNum && this.isPageRead(pageNum)) {
                    const pIdx = this.readMushafPages.indexOf(pageNum);
                    if (pIdx >= 0) this.readMushafPages.splice(pIdx, 1);
                    localStorage.setItem('quran_read_pages', JSON.stringify(this.readMushafPages));
                }
            } else {
                verses.push(verseNumber);
                saveReadVerses(surahNumber, verses);
                if (pageNum && !this.isPageRead(pageNum) && this.isPageFullyReadByVerses(pageNum)) {
                    this.readMushafPages.push(pageNum);
                    localStorage.setItem('quran_read_pages', JSON.stringify(this.readMushafPages));
                    const meta = pageMetaMap[pageNum] || pageMetaMap[String(pageNum)];
                    this.submitKhatamPage(pageNum, meta);
                }
            }
            this.bumpReadUi();
        },

        formatKhatamPercent() {
            const p = Number(this.khatamProgressPercent) || 0;
            return (Math.round(p * 10) / 10) + '%';
        },

        formatLocalReadSummary() {
            void this._readTick;
            const pagesInView = [...new Set(playlist.map(v => v.page_number).filter(Boolean))];
            const readPages = pagesInView.filter(p => this.isPageRead(p)).length;
            const readVerses = playlist.filter(v => this.isVerseRead(v.surah_number, v.verse_number)).length;
            return 'Tampilan ini: ' + readPages + '/' + pagesInView.length + ' hlm · ' + readVerses + '/' + playlist.length + ' ayat';
        },

        submitKhatamPage(pageNum, meta) {
            if (!this.khatamEnabled || !config.khatamProgressUrl || !meta) return;

            const submitted = getKhatamSubmittedPages();
            const pageKey = pageNum + ':' + meta.surah_number + ':' + meta.verse_from + ':' + meta.verse_to;
            if (submitted.includes(pageKey)) return;

            fetch(config.khatamProgressUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    surah_number: meta.surah_number,
                    verse_from: meta.verse_from,
                    verse_to: meta.verse_to,
                    juz_number: meta.juz_number,
                    pages_read: 1,
                }),
            })
            .then(r => {
                if (!r.ok) throw new Error('khatam failed');
                return r.json();
            })
            .then(data => {
                if (!data.success) return;

                if (!submitted.includes(pageKey)) {
                    submitted.push(pageKey);
                    saveKhatamSubmittedPages(submitted);
                }

                if (typeof data.progress_percent === 'number') {
                    this.khatamProgressPercent = data.progress_percent;
                }
                if (typeof data.total_pages_read === 'number') {
                    this.khatamPagesRead = data.total_pages_read;
                }

                if (!data.duplicate && typeof showToast === 'function') {
                    showToast('Progress khatam +1 halaman', 'success');
                }
            })
            .catch(() => {
                if (typeof showToast === 'function') showToast('Gagal menyimpan progress khatam', 'error');
            });
        },

        isPageRead(pageNum) {
            void this._readTick;
            if (this.readMushafPages.includes(pageNum)) return true;
            return this.isPageFullyReadByVerses(pageNum);
        },

        markPageRead(pageNum) {
            const idx = this.readMushafPages.indexOf(pageNum);
            const wasRead = idx >= 0 || this.isPageFullyReadByVerses(pageNum);

            if (wasRead) {
                if (idx >= 0) this.readMushafPages.splice(idx, 1);
                this.unmarkAllVersesOnPage(pageNum);
            } else {
                if (idx < 0) this.readMushafPages.push(pageNum);
                this.markAllVersesOnPage(pageNum, { silent: true });
                if (this.khatamEnabled) {
                    const meta = pageMetaMap[pageNum] || pageMetaMap[String(pageNum)];
                    this.submitKhatamPage(pageNum, meta);
                }
            }

            localStorage.setItem('quran_read_pages', JSON.stringify(this.readMushafPages));
            this.bumpReadUi();
        },

        markPageVersesRead(pageNum) {
            if (pageNum) this.markAllVersesOnPage(pageNum);
        },

        playAudio(surahNumber, verseNumber, url) {
            if (!url) return;
            if (this.currentSurah === surahNumber && this.currentAyat === verseNumber && this.isPlaying) {
                this.audio.pause();
                this.isPlaying = false;
                return;
            }
            this.stopAudio();
            this.currentSurah = surahNumber;
            this.currentAyat = verseNumber;
            const item = playlist.find(v => v.surah_number === surahNumber && v.verse_number === verseNumber);
            this.currentAyatLabel = item
                ? item.surah_name + ' : ' + verseNumber
                : (config.defaultSurahName || 'Ayat') + ' ' + verseNumber;

            this.audio = new Audio(url);
            this.audio.play().then(() => { this.isPlaying = true; }).catch(() => {});
            this.audio.ontimeupdate = () => {
                if (this.audio?.duration) this.audioProgress = (this.audio.currentTime / this.audio.duration) * 100;
            };
            this.audio.onended = () => {
                this.highlightVerse(surahNumber, verseNumber, false);
                this.playNextInPlaylist(surahNumber, verseNumber);
            };
            this.highlightVerse(surahNumber, verseNumber, true);
            this.saveLastRead(surahNumber, verseNumber, item?.surah_id || config.defaultSurahId);
        },

        playNextInPlaylist(surahNumber, verseNumber) {
            const key = this.ayatKey(surahNumber, verseNumber);
            const idx = playlistIndex[key];
            if (idx === undefined || idx >= playlist.length - 1) {
                this.stopAudio();
                return;
            }
            const next = playlist[idx + 1];
            this.playAudio(next.surah_number, next.verse_number, next.audio_url);
        },

        playPrevInPlaylist() {
            if (!this.currentSurah || !this.currentAyat) return;
            const key = this.ayatKey(this.currentSurah, this.currentAyat);
            const idx = playlistIndex[key];
            if (idx === undefined || idx <= 0) return;
            const prev = playlist[idx - 1];
            this.playAudio(prev.surah_number, prev.verse_number, prev.audio_url);
        },

        playNextInPlaylistManual() {
            if (!this.currentSurah || !this.currentAyat) return;
            this.playNextInPlaylist(this.currentSurah, this.currentAyat);
        },

        togglePlay() {
            if (!this.audio) return;
            if (this.isPlaying) { this.audio.pause(); this.isPlaying = false; }
            else { this.audio.play(); this.isPlaying = true; }
        },

        stopAudio() {
            if (this.audio) { this.audio.pause(); this.audio.currentTime = 0; this.audio = null; }
            if (this.currentSurah && this.currentAyat) {
                this.highlightVerse(this.currentSurah, this.currentAyat, false);
            }
            this.isPlaying = false;
            this.currentSurah = 0;
            this.currentAyat = 0;
            this.currentAyatLabel = '';
            this.audioProgress = 0;
        },

        highlightVerse(surahNumber, verseNumber, on) {
            const ids = ['ayat-' + verseNumber, 'ayat-' + surahNumber + '-' + verseNumber];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                if (on) {
                    el.classList.add('verse-playing');
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    el.classList.remove('verse-playing');
                }
            });
        },

        toggleBookmark(surahId, verseNumber, btn) {
            @auth
            fetch(@json(route('quran.bookmark')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ surah_id: surahId, verse_number: verseNumber }),
            }).then(r => {
                if (!r.ok) throw new Error('bookmark failed');
                return r.json();
            }).then(data => {
                const svg = btn.querySelector('svg');
                const key = surahId + ':' + verseNumber;
                if (data.bookmarked) {
                    bookmarkSet.add(key);
                    btn.classList.remove('text-gray-400');
                    btn.classList.add('text-amber-500');
                    svg?.setAttribute('fill', 'currentColor');
                } else {
                    bookmarkSet.delete(key);
                    btn.classList.remove('text-amber-500');
                    btn.classList.add('text-gray-400');
                    svg?.setAttribute('fill', 'none');
                }
            }).catch(() => {
                if (typeof showToast === 'function') showToast('Gagal menyimpan bookmark', 'error');
            });
            @else
            if (typeof showToast === 'function') showToast('Login untuk menyimpan bookmark', 'warning');
            @endauth
        },

        isBookmarked(surahId, verseNumber) {
            return bookmarkSet.has(surahId + ':' + verseNumber);
        },

        saveLastRead(surahNumber, verseNumber, surahId) {
            const item = playlist.find(v => v.surah_number === surahNumber && v.verse_number === verseNumber);
            const name = item?.surah_name || config.defaultSurahName || '';
            localStorage.setItem('quran_last_read', JSON.stringify({
                surah_number: surahNumber,
                surah_name: name,
                verse: verseNumber,
            }));

            if (!surahId) return;
            clearTimeout(this._lastReadTimeout);
            this._lastReadTimeout = setTimeout(() => {
                @auth
                fetch(@json(route('quran.last-read')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ surah_id: surahId, verse_number: verseNumber }),
                }).catch(() => {});
                @endauth
            }, 1500);
        },

        initReaderScroll() {
            if (this._scrollBound) return;
            this._scrollBound = true;
            window.addEventListener('scroll', () => {
                if (this.viewMode !== 'list') return;
                const items = document.querySelectorAll('.verse-item[data-surah][data-verse]');
                let visible = null;
                for (const item of items) {
                    const rect = item.getBoundingClientRect();
                    if (rect.top >= 0 && rect.top < window.innerHeight * 0.5) {
                        visible = item;
                        break;
                    }
                }
                if (visible) {
                    const sn = parseInt(visible.dataset.surah, 10);
                    const vn = parseInt(visible.dataset.verse, 10);
                    const item = playlist.find(v => v.surah_number === sn && v.verse_number === vn);
                    this.saveLastRead(sn, vn, item?.surah_id || config.defaultSurahId);
                }
            });
        },

    };
};
</script>
@endpush
@endonce
