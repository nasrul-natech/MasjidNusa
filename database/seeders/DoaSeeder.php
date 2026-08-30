<?php

namespace Database\Seeders;

use App\Models\Doa;
use App\Models\DoaCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Doa Harian', 'slug' => 'doa-harian', 'icon' => 'book', 'sort_order' => 1],
            ['name' => 'Doa Haji & Umrah', 'slug' => 'doa-haji-umrah', 'icon' => 'kaaba', 'sort_order' => 2],
            ['name' => 'Doa Bepergian', 'slug' => 'doa-bepergian', 'icon' => 'plane', 'sort_order' => 3],
            ['name' => 'Bacaan Shalat', 'slug' => 'bacaan-shalat', 'icon' => 'pray', 'sort_order' => 4],
            ['name' => 'Hadits Pilihan', 'slug' => 'hadits-pilihan', 'icon' => 'hadith', 'sort_order' => 5],
            ['name' => 'Zikir Pagi & Petang', 'slug' => 'zikir-pagi-petang', 'icon' => 'sun', 'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            DoaCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        $duas = [
            // ===== DOA HARIAN =====
            [
                'category' => 'doa-harian',
                'title' => 'Doa Sebelum Makan',
                'arabic' => 'اللَّهُمَّ بَارِكْ لَنَا فِيمَا رَزَقْتَنَا وَقِنَا عَذَابَ النَّارِ بِسْمِ اللَّهِ',
                'latin' => "Allāhumma bārik lanā fīmā razaqtanā wa qinā 'adzāban-nār, bismillāh",
                'translation' => 'Ya Allah, berkahilah kami dalam rezeki yang telah Engkau berikan kepada kami, dan lindungilah kami dari siksa api neraka. Dengan nama Allah.',
                'source' => "Ibnu As-Sunni, 'Amalul Yaum wal Lailah no. 456",
                'sort_order' => 1,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Sesudah Makan',
                'arabic' => 'الْحَمْدُ لِلَّهِ الَّذِي أَطْعَمَنَا وَسَقَانَا وَجَعَلَنَا مُسْلِمِينَ',
                'latin' => "Alḥamdulillāhil-ladzī ath'amanā wa saqānā wa ja'alanā muslimīn",
                'translation' => 'Segala puji bagi Allah yang telah memberi kami makan dan minum, serta menjadikan kami sebagai orang-orang muslim.',
                'source' => 'HR. Abu Dawud no. 3850, At-Tirmidzi no. 3457',
                'sort_order' => 2,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Sebelum Tidur',
                'arabic' => 'بِاسْمِكَ اللَّهُمَّ أَمُوتُ وَأَحْيَا',
                'latin' => "Bismikallāhumma amūtu wa aḥyā",
                'translation' => 'Dengan nama-Mu ya Allah, aku mati dan aku hidup.',
                'source' => 'HR. Bukhari no. 6312, Muslim no. 2711',
                'sort_order' => 3,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Bangun Tidur',
                'arabic' => 'الْحَمْدُ لِلَّهِ الَّذِي أَحْيَانَا بَعْدَ مَا أَمَاتَنَا وَإِلَيْهِ النُّشُورُ',
                'latin' => "Alḥamdulillāhil-ladzī aḥyānā ba'da mā amātanā wa ilaihin-nusyūr",
                'translation' => 'Segala puji bagi Allah yang telah menghidupkan kami setelah mematikan kami, dan kepada-Nya-lah kami akan dibangkitkan.',
                'source' => 'HR. Bukhari no. 6312',
                'sort_order' => 4,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Masuk Kamar Mandi',
                'arabic' => 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْخُبُثِ وَالْخَبَائِثِ',
                'latin' => "Allāhumma innī a'ūdzu bika minal-khubutsi wal-khabā'its",
                'translation' => 'Ya Allah, sesungguhnya aku berlindung kepada-Mu dari setan laki-laki dan setan perempuan.',
                'source' => 'HR. Bukhari no. 142, Muslim no. 375',
                'sort_order' => 5,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Keluar Kamar Mandi',
                'arabic' => 'غُفْرَانَكَ',
                'latin' => "Ghufrānak",
                'translation' => 'Aku memohon ampunan-Mu (ya Allah).',
                'source' => 'HR. Abu Dawud no. 30, At-Tirmidzi no. 7, Ibnu Majah no. 300',
                'sort_order' => 6,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Keluar Rumah',
                'arabic' => 'بِسْمِ اللَّهِ تَوَكَّلْتُ عَلَى اللَّهِ وَلَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللَّهِ',
                'latin' => "Bismillāhi tawakkaltu 'alallāhi wa lā ḥawla wa lā quwwata illā billāh",
                'translation' => 'Dengan nama Allah, aku bertawakkal kepada Allah, tidak ada daya dan kekuatan kecuali dengan (pertolongan) Allah.',
                'source' => 'HR. Abu Dawud no. 5095, At-Tirmidzi no. 3426',
                'sort_order' => 7,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Masuk Rumah',
                'arabic' => 'بِسْمِ اللَّهِ وَلَجْنَا وَبِسْمِ اللَّهِ خَرَجْنَا وَعَلَى رَبِّنَا تَوَكَّلْنَا',
                'latin' => "Bismillāhi walajnā wa bismillāhi kharajnā wa 'alā rabbinā tawakkalnā",
                'translation' => 'Dengan nama Allah kami masuk, dengan nama Allah kami keluar, dan kepada Tuhan kami, kami bertawakkal.',
                'source' => 'HR. Abu Dawud no. 5096',
                'sort_order' => 8,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Masuk Masjid',
                'arabic' => 'اللَّهُمَّ افْتَحْ لِي أَبْوَابَ رَحْمَتِكَ',
                'latin' => "Allāhummaftaḥ lī abwāba raḥmatik",
                'translation' => 'Ya Allah, bukakanlah untukku pintu-pintu rahmat-Mu.',
                'source' => 'HR. Muslim no. 713',
                'sort_order' => 9,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Keluar Masjid',
                'arabic' => 'اللَّهُمَّ إِنِّي أَسْأَلُكَ مِنْ فَضْلِكَ',
                'latin' => "Allāhumma innī as'aluka min faḍlik",
                'translation' => 'Ya Allah, sesungguhnya aku memohon kepada-Mu dari karunia-Mu.',
                'source' => 'HR. Muslim no. 713',
                'sort_order' => 10,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Ketika Bersin',
                'arabic' => 'الْحَمْدُ لِلَّهِ',
                'latin' => "Alḥamdulillāh",
                'translation' => 'Segala puji bagi Allah.',
                'source' => 'HR. Bukhari no. 6224',
                'content_text' => "Jika ada orang yang bersin dan mengucapkan 'Alhamdulillah', maka jawablah dengan 'Yarhamukallah' (Semoga Allah merahmatimu). Kemudian yang bersin menjawab 'Yahdikumullah wa yushlihu baalakum' (Semoga Allah memberi petunjuk kepadamu dan memperbaiki keadaanmu).",
                'sort_order' => 11,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Berpakaian',
                'arabic' => 'الْحَمْدُ لِلَّهِ الَّذِي كَسَانِي هَذَا وَرَزَقَنِيهِ مِنْ غَيْرِ حَوْلٍ مِنِّي وَلَا قُوَّةٍ',
                'latin' => "Alḥamdulillāhil-ladzī kasānī hādzā wa razaqanīhi min ghairi ḥawlin minnī wa lā quwwah",
                'translation' => 'Segala puji bagi Allah yang telah memberiku pakaian ini dan memberikannya kepadaku tanpa daya dan kekuatan dariku.',
                'source' => 'HR. Abu Dawud no. 4023, At-Tirmidzi no. 3458',
                'sort_order' => 12,
            ],
            [
                'category' => 'doa-harian',
                'title' => 'Doa Bercermin',
                'arabic' => 'اللَّهُمَّ كَمَا أَحْسَنْتَ خَلْقِي فَأَحْسِنْ خُلُقِي',
                'latin' => "Allāhumma kamā aḥsanta khalqī fa aḥsin khuluqī",
                'translation' => 'Ya Allah, sebagaimana Engkau telah membaguskan penciptaanku, maka baguskanlah akhlakku.',
                'source' => 'HR. Ahmad no. 3823',
                'sort_order' => 13,
            ],

            // ===== DOA HAJI & UMRAH =====
            [
                'category' => 'doa-haji-umrah',
                'title' => 'Doa Niat Ihram Umrah',
                'arabic' => 'لَبَّيْكَ اللَّهُمَّ عُمْرَةً',
                'latin' => "Labbaikallahumma 'umratan",
                'translation' => 'Aku penuhi panggilan-Mu ya Allah untuk umrah.',
                'source' => 'HR. Bukhari no. 1551, Muslim no. 1232',
                'sort_order' => 1,
            ],
            [
                'category' => 'doa-haji-umrah',
                'title' => 'Bacaan Talbiyah',
                'arabic' => 'لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ، لَبَّيْكَ لَا شَرِيكَ لَكَ لَبَّيْكَ، إِنَّ الْحَمْدَ وَالنِّعْمَةَ لَكَ وَالْمُلْكَ، لَا شَرِيكَ لَكَ',
                'latin' => "Labbaik Allāhumma labbaik, labbaik lā syarīka laka labbaik. Innal-ḥamda wan-ni'mata laka wal-mulk, lā syarīka lak",
                'translation' => 'Aku penuhi panggilan-Mu ya Allah, aku penuhi panggilan-Mu. Aku penuhi panggilan-Mu, tiada sekutu bagi-Mu, aku penuhi panggilan-Mu. Sesungguhnya segala puji, nikmat, dan kerajaan hanya milik-Mu, tiada sekutu bagi-Mu.',
                'source' => 'HR. Bukhari no. 1549, Muslim no. 1184',
                'sort_order' => 2,
            ],
            [
                'category' => 'doa-haji-umrah',
                'title' => 'Doa di Depan Hajar Aswad',
                'arabic' => 'بِسْمِ اللَّهِ وَاللَّهُ أَكْبَرُ',
                'latin' => "Bismillāhi wallāhu akbar",
                'translation' => 'Dengan nama Allah, Allah Maha Besar.',
                'source' => 'HR. Ahmad, Ath-Thabrani (Shahih menurut Ibnu Khuzaimah)',
                'sort_order' => 3,
            ],
            [
                'category' => 'doa-haji-umrah',
                'title' => 'Doa Thawaf (antara Rukun Yamani dan Hajar Aswad)',
                'arabic' => 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
                'latin' => "Rabbanā ātinā fid-dunyā ḥasanatan wa fil-ākhirati ḥasanatan wa qinā 'adzāban-nār",
                'translation' => 'Ya Tuhan kami, berilah kami kebaikan di dunia dan kebaikan di akhirat, dan lindungilah kami dari siksa api neraka.',
                'source' => 'QS. Al-Baqarah [2]: 201; HR. Abu Dawud no. 1892',
                'sort_order' => 4,
            ],
            [
                'category' => 'doa-haji-umrah',
                'title' => "Doa Sa'i di Bukit Shafa",
                'arabic' => 'إِنَّ الصَّفَا وَالْمَرْوَةَ مِنْ شَعَائِرِ اللَّهِ، أَبْدَأُ بِمَا بَدَأَ اللَّهُ بِهِ',
                'latin' => "Innaṣ-ṣafā wal-marwata min sya'ā'irillāh, abda'u bimā bada'allāhu bih",
                'translation' => "Sesungguhnya Shafa dan Marwah termasuk syiar-syiar Allah. Aku mulai dengan apa yang Allah mulai.",
                'source' => 'HR. Muslim no. 1218',
                'sort_order' => 5,
            ],
            [
                'category' => 'doa-haji-umrah',
                'title' => 'Doa di Arafah',
                'arabic' => 'لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ',
                'latin' => "Lā ilāha illallāhu waḥdahu lā syarīka lah, lahul-mulku wa lahul-ḥamdu wa huwa 'alā kulli syai'in qadīr",
                'translation' => 'Tiada tuhan selain Allah semata, tiada sekutu bagi-Nya. Milik-Nya kerajaan dan segala pujian, dan Dia Maha Kuasa atas segala sesuatu.',
                'source' => 'HR. At-Tirmidzi no. 3585',
                'sort_order' => 6,
            ],

            // ===== DOA BEPERGIAN =====
            [
                'category' => 'doa-bepergian',
                'title' => 'Doa Naik Kendaraan',
                'arabic' => 'سُبْحَانَ الَّذِي سَخَّرَ لَنَا هَذَا وَمَا كُنَّا لَهُ مُقْرِنِينَ وَإِنَّا إِلَى رَبِّنَا لَمُنْقَلِبُونَ',
                'latin' => "Subḥānalladzī sakhkhara lanā hādzā wa mā kunnā lahū muqrinīn, wa innā ilā rabbinā lamunqalibūn",
                'translation' => 'Maha Suci Dzat yang telah menundukkan semua ini bagi kami padahal kami sebelumnya tidak mampu menguasainya, dan sesungguhnya kami akan kembali kepada Tuhan kami.',
                'source' => 'QS. Az-Zukhruf [43]: 13-14; HR. Muslim no. 1342',
                'sort_order' => 1,
            ],
            [
                'category' => 'doa-bepergian',
                'title' => 'Doa Bepergian (Safar)',
                'arabic' => 'اللَّهُمَّ إِنَّا نَسْأَلُكَ فِي سَفَرِنَا هَذَا الْبِرَّ وَالتَّقْوَى وَمِنَ الْعَمَلِ مَا تَرْضَى، اللَّهُمَّ هَوِّنْ عَلَيْنَا سَفَرَنَا هَذَا وَاطْوِ عَنَّا بُعْدَهُ',
                'latin' => "Allāhumma innā nas'aluka fī safarinā hādza al-birra wat-taqwā wa minal-'amali mā tarḍā. Allāhumma hawwin 'alainā safaranā hādzā waṭwi 'annā bu'dah",
                'translation' => 'Ya Allah, sesungguhnya kami memohon kepada-Mu dalam perjalanan kami ini kebaikan dan takwa, serta amal yang Engkau ridhai. Ya Allah, mudahkanlah perjalanan kami ini dan dekatkanlah jaraknya.',
                'source' => 'HR. Muslim no. 1342',
                'sort_order' => 2,
            ],
            [
                'category' => 'doa-bepergian',
                'title' => 'Doa Tiba di Tempat Tujuan',
                'arabic' => 'أَعُوذُ بِكَلِمَاتِ اللَّهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ',
                'latin' => "A'ūdzu bikalimātillāhit-tāmmāti min syarri mā khalaq",
                'translation' => 'Aku berlindung dengan kalimat-kalimat Allah yang sempurna dari keburukan semua makhluk-Nya.',
                'source' => 'HR. Muslim no. 2708',
                'sort_order' => 3,
            ],
            [
                'category' => 'doa-bepergian',
                'title' => 'Doa Pulang dari Bepergian',
                'arabic' => 'آيِبُونَ تَائِبُونَ عَابِدُونَ لِرَبِّنَا حَامِدُونَ',
                'latin' => "Āyibūna tā'ibūna 'ābidūna lirabbinā ḥāmidūn",
                'translation' => 'Kami kembali, bertaubat, beribadah, dan memuji Tuhan kami.',
                'source' => 'HR. Bukhari no. 1797, Muslim no. 1342',
                'sort_order' => 4,
            ],
            [
                'category' => 'doa-bepergian',
                'title' => 'Doa Memasuki Kota/Desa',
                'arabic' => 'اللَّهُمَّ رَبَّ السَّمَاوَاتِ السَّبْعِ وَمَا أَظْلَلْنَ وَرَبَّ الْأَرَضِينَ السَّبْعِ وَمَا أَقْلَلْنَ وَرَبَّ الشَّيَاطِينِ وَمَا أَضْلَلْنَ وَرَبَّ الرِّيَاحِ وَمَا ذَرَيْنَ أَسْأَلُكَ خَيْرَ هَذِهِ الْقَرْيَةِ وَخَيْرَ أَهْلِهَا وَخَيْرَ مَا فِيهَا وَأَعُوذُ بِكَ مِنْ شَرِّهَا وَشَرِّ أَهْلِهَا وَشَرِّ مَا فِيهَا',
                'latin' => "Allāhumma rabbas-samāwātis-sab'i wa mā aẓlalna, wa rabbal-araḍīnas-sab'i wa mā aqlalna, wa rabbasy-syayāṭīni wa mā aḍlalna, wa rabbar-riyāḥi wa mā dzaraina. As'aluka khaira hādzihil-qaryati wa khaira ahlihā wa khaira mā fīhā, wa a'ūdzu bika min syarrihā wa syarri ahlihā wa syarri mā fīhā.",
                'translation' => 'Ya Allah, Tuhan langit yang tujuh dan apa yang dinaunginya, Tuhan bumi yang tujuh dan apa yang dipikulnya, Tuhan setan dan apa yang disesatkannya, Tuhan angin dan apa yang diterbangkannya. Aku memohon kebaikan kota ini, kebaikan penduduknya dan kebaikan yang ada di dalamnya. Dan aku berlindung kepada-Mu dari keburukannya, keburukan penduduknya dan keburukan yang ada di dalamnya.',
                'source' => "HR. Ibnu Hibban, An-Nasa'i dalam Al-Kubra, Al-Hakim (shahih)",
                'sort_order' => 5,
            ],

            // ===== BACAAN SHALAT =====
            [
                'category' => 'bacaan-shalat',
                'title' => "Doa Iftitah (Pembuka Shalat)",
                'arabic' => 'وَجَّهْتُ وَجْهِيَ لِلَّذِي فَطَرَ السَّمَاوَاتِ وَالْأَرْضَ حَنِيفًا وَمَا أَنَا مِنَ الْمُشْرِكِينَ، إِنَّ صَلَاتِي وَنُسُكِي وَمَحْيَايَ وَمَمَاتِي لِلَّهِ رَبِّ الْعَالَمِينَ، لَا شَرِيكَ لَهُ وَبِذَلِكَ أُمِرْتُ وَأَنَا مِنَ الْمُسْلِمِينَ',
                'latin' => "Wajjahtu wajhiya lilladzī faṭaras-samāwāti wal-arḍa ḥanīfan wa mā ana minal-musyrikīn. Inna ṣalātī wa nusukī wa maḥyāya wa mamātī lillāhi rabbil-'ālamīn. Lā syarīka lahū wa bidzālika umirtu wa ana minal-muslimīn",
                'translation' => 'Aku hadapkan wajahku kepada Dzat yang menciptakan langit dan bumi dalam keadaan hanif (lurus), dan aku bukan termasuk orang-orang yang musyrik. Sesungguhnya shalatku, ibadahku, hidupku, dan matiku hanya untuk Allah, Tuhan semesta alam. Tiada sekutu bagi-Nya, dan demikianlah yang diperintahkan kepadaku, dan aku termasuk orang-orang yang muslim.',
                'source' => 'HR. Muslim no. 771',
                'sort_order' => 1,
            ],
            [
                'category' => 'bacaan-shalat',
                'title' => "Doa Ruku'",
                'arabic' => 'سُبْحَانَ رَبِّيَ الْعَظِيمِ',
                'latin' => "Subḥāna rabbiyal-'aẓīm",
                'translation' => 'Maha Suci Tuhanku Yang Maha Agung.',
                'source' => 'HR. Muslim no. 772, Abu Dawud no. 871',
                'content_text' => "Dibaca minimal 3x saat ruku'. Rasulullah shallallahu 'alaihi wa sallam membacanya tiga kali atau lebih.",
                'sort_order' => 2,
            ],
            [
                'category' => 'bacaan-shalat',
                'title' => "Doa I'tidal (Bangkit dari Ruku')",
                'arabic' => 'رَبَّنَا وَلَكَ الْحَمْدُ حَمْدًا كَثِيرًا طَيِّبًا مُبَارَكًا فِيهِ',
                'latin' => "Rabbanā wa lakal-ḥamdu ḥamdan katsīran ṭhayyiban mubārakan fīh",
                'translation' => 'Wahai Tuhan kami, bagi-Mu segala puji, pujian yang banyak, yang baik, dan penuh berkah.',
                'source' => 'HR. Bukhari no. 799',
                'sort_order' => 3,
            ],
            [
                'category' => 'bacaan-shalat',
                'title' => 'Doa Sujud',
                'arabic' => 'سُبْحَانَ رَبِّيَ الْأَعْلَى',
                'latin' => "Subḥāna rabbiyal-a'lā",
                'translation' => 'Maha Suci Tuhanku Yang Maha Tinggi.',
                'source' => 'HR. Muslim no. 772, Abu Dawud no. 871',
                'content_text' => "Dibaca minimal 3x saat sujud. Sujud merupakan posisi terdekat seorang hamba kepada Rabbnya, maka perbanyaklah doa di dalamnya.",
                'sort_order' => 4,
            ],
            [
                'category' => 'bacaan-shalat',
                'title' => 'Doa Duduk antara Dua Sujud',
                'arabic' => 'رَبِّ اغْفِرْ لِي، رَبِّ اغْفِرْ لِي',
                'latin' => "Rabbighfir lī, rabbighfir lī",
                'translation' => 'Ya Tuhanku, ampunilah aku. Ya Tuhanku, ampunilah aku.',
                'source' => 'HR. Abu Dawud no. 874, Ibnu Majah no. 897',
                'sort_order' => 5,
            ],
            [
                'category' => 'bacaan-shalat',
                'title' => 'Doa Tasyahhud Akhir (Tahiyyat)',
                'arabic' => 'التَّحِيَّاتُ لِلَّهِ وَالصَّلَوَاتُ وَالطَّيِّبَاتُ، السَّلَامُ عَلَيْكَ أَيُّهَا النَّبِيُّ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ، السَّلَامُ عَلَيْنَا وَعَلَى عِبَادِ اللَّهِ الصَّالِحِينَ، أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَأَشْهَدُ أَنَّ مُحَمَّدًا عَبْدُهُ وَرَسُولُهُ',
                'latin' => "At-taḥiyyātu lillāhi waṣ-ṣalawātu waṭ-ṭayyibāt. As-salāmu 'alaika ayyuhan-nabiyyu wa raḥmatullāhi wa barakātuh. As-salāmu 'alainā wa 'alā 'ibādillāhiṣ-ṣāliḥīn. Asyhadu an lā ilāha illallāh, wa asyhadu anna Muḥammadan 'abduhū wa rasūluh",
                'translation' => 'Segala penghormatan, shalawat, dan kebaikan hanya milik Allah. Salam sejahtera atasmu wahai Nabi, dan rahmat Allah serta berkah-Nya. Salam sejahtera atas kami dan atas hamba-hamba Allah yang shalih. Aku bersaksi bahwa tiada tuhan selain Allah, dan aku bersaksi bahwa Muhammad adalah hamba dan utusan-Nya.',
                'source' => 'HR. Bukhari no. 831, Muslim no. 402',
                'sort_order' => 6,
            ],
            [
                'category' => 'bacaan-shalat',
                'title' => 'Doa Setelah Tasyahhud Akhir Sebelum Salam',
                'arabic' => 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنْ عَذَابِ جَهَنَّمَ، وَمِنْ عَذَابِ الْقَبْرِ، وَمِنْ فِتْنَةِ الْمَحْيَا وَالْمَمَاتِ، وَمِنْ شَرِّ فِتْنَةِ الْمَسِيحِ الدَّجَّالِ',
                'latin' => "Allāhumma innī a'ūdzu bika min 'adzābi jahannam, wa min 'adzābil-qabr, wa min fitnatil-maḥyā wal-mamāt, wa min syarri fitnatil-masīḥid-dajjāl",
                'translation' => 'Ya Allah, sesungguhnya aku berlindung kepada-Mu dari siksa Jahannam, dari siksa kubur, dari fitnah hidup dan mati, dan dari keburukan fitnah Dajjal.',
                'source' => 'HR. Bukhari no. 1377, Muslim no. 588',
                'sort_order' => 7,
            ],
            [
                'category' => 'bacaan-shalat',
                'title' => 'Doa Qunut Nazilah',
                'arabic' => 'اللَّهُمَّ اهْدِنَا فِيمَنْ هَدَيْتَ، وَعَافِنَا فِيمَنْ عَافَيْتَ، وَتَوَلَّنَا فِيمَنْ تَوَلَّيْتَ، وَبَارِكْ لَنَا فِيمَا أَعْطَيْتَ، وَقِنَا شَرَّ مَا قَضَيْتَ، فَإِنَّكَ تَقْضِي وَلَا يُقْضَى عَلَيْكَ، وَإِنَّهُ لَا يَذِلُّ مَنْ وَالَيْتَ، تَبَارَكْتَ رَبَّنَا وَتَعَالَيْتَ',
                'latin' => "Allāhummahdīnā fīman hadait, wa 'āfinā fīman 'āfait, wa tawallanā fīman tawallait, wa bārik lanā fīmā a'ṭait, wa qinā syarra mā qaḍait, fa innaka taqḍī wa lā yuqḍā 'alaik. Wa innahū lā yadzillu man wālait. Tabārakta rabbanā wa ta'ālait",
                'translation' => 'Ya Allah, berilah kami petunjuk di antara orang yang Engkau beri petunjuk, berilah kami keselamatan di antara orang yang Engkau beri keselamatan, uruslah kami di antara orang yang Engkau urus, berkahilah kami pada apa yang Engkau berikan, lindungilah kami dari keburukan apa yang Engkau tetapkan. Sesungguhnya Engkau yang menetapkan dan tidak ada yang menetapkan atas-Mu. Sesungguhnya tidak akan hina orang yang Engkau tolong. Maha Suci Engkau wahai Tuhan kami dan Maha Tinggi.',
                'source' => 'HR. Abu Dawud no. 1425, At-Tirmidzi no. 464, An-Nasa\'i no. 1745',
                'sort_order' => 8,
            ],

            // ===== HADITS PILIHAN =====
            [
                'category' => 'hadits-pilihan',
                'title' => 'Sayyidul Istighfar (Penghulu Istighfar)',
                'arabic' => 'اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَهَ إِلَّا أَنْتَ خَلَقْتَنِي وَأَنَا عَبْدُكَ وَأَنَا عَلَى عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ وَأَبُوءُ بِذَنْبِي فَاغْفِرْ لِي فَإِنَّهُ لَا يَغْفِرُ الذُّنُوبَ إِلَّا أَنْتَ',
                'latin' => "Allāhumma anta rabbī lā ilāha illā anta, khalaqtanī wa ana 'abduka, wa ana 'alā 'ahdika wa wa'dika mastaṭa'tu. A'ūdzu bika min syarri mā ṣana'tu. Abū'u laka bini'matika 'alayya wa abū'u bidzanbī faghfir lī fa innahū lā yaghfirudz-dzunūba illā ant",
                'translation' => 'Ya Allah, Engkau adalah Tuhanku, tiada tuhan selain Engkau. Engkau telah menciptakanku dan aku adalah hamba-Mu. Aku berada di atas perjanjian-Mu dan janji-Mu semampuku. Aku berlindung kepada-Mu dari keburukan apa yang aku perbuat. Aku mengakui nikmat-Mu kepadaku dan aku mengakui dosaku. Maka ampunilah aku, karena sesungguhnya tidak ada yang mengampuni dosa kecuali Engkau.',
                'source' => 'HR. Bukhari no. 6306',
                'content_text' => "Rasulullah shallallahu 'alaihi wa sallam bersabda: 'Barangsiapa membacanya di waktu siang dengan penuh keyakinan, lalu ia mati pada hari itu sebelum petang, maka ia termasuk ahli surga. Dan barangsiapa membacanya di waktu malam dengan penuh keyakinan, lalu ia mati sebelum pagi, maka ia termasuk ahli surga.'",
                'sort_order' => 1,
            ],
            [
                'category' => 'hadits-pilihan',
                'title' => 'Dzikir Pagi dan Petang (Ayat Kursi)',
                'arabic' => 'اللَّهُ لَا إِلَهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ لَهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ مَنْ ذَا الَّذِي يَشْفَعُ عِنْدَهُ إِلَّا بِإِذْنِهِ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ وَلَا يُحِيطُونَ بِشَيْءٍ مِنْ عِلْمِهِ إِلَّا بِمَا شَاءَ وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالْأَرْضَ وَلَا يَئُودُهُ حِفْظُهُمَا وَهُوَ الْعَلِيُّ الْعَظِيمُ',
                'latin' => "Allāhu lā ilāha illā huwal-ḥayyul-qayyūm. Lā ta'khudzuhū sinatun walā naum. Lahū mā fis-samāwāti wa mā fil-arḍ. Man dzalladzī yasyfa'u 'indahū illā bi'idznih. Ya'lamu mā baina aidīhim wa mā khalfahum. Wa lā yuḥīṭūna bisyai'in min 'ilmihī illā bimā syā'. Wasi'a kursiyyuhus-samāwāti wal-arḍ. Wa lā ya'ūduhū ḥifẓuhumā wa huwal-'aliyyul-'aẓīm",
                'translation' => 'Allah, tiada tuhan selain Dia, Yang Maha Hidup lagi terus-menerus mengurus (makhluk-Nya). Dia tidak mengantuk dan tidak tidur. Milik-Nya apa yang ada di langit dan apa yang ada di bumi. Tidak ada yang dapat memberi syafaat di sisi-Nya tanpa izin-Nya. Dia mengetahui apa yang ada di hadapan mereka dan apa yang ada di belakang mereka. Dan mereka tidak mengetahui sesuatu pun dari ilmu-Nya melainkan apa yang Dia kehendaki. Kursi-Nya meliputi langit dan bumi. Dan Dia tidak merasa berat memelihara keduanya. Dan Dia Maha Tinggi lagi Maha Agung.',
                'source' => 'QS. Al-Baqarah [2]: 255; HR. An-Nasa\'i dalam Al-Kubra',
                'content_text' => "Rasulullah shallallahu 'alaihi wa sallam bersabda: 'Barangsiapa membaca Ayat Kursi setiap selesai shalat fardhu, maka tidak ada yang menghalanginya masuk surga kecuali kematian.' (HR. An-Nasa'i, dishahihkan oleh Al-Albani).",
                'sort_order' => 2,
            ],
            [
                'category' => 'hadits-pilihan',
                'title' => 'Doa Perlindungan dari Segala Keburukan',
                'arabic' => 'أَعُوذُ بِكَلِمَاتِ اللَّهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ',
                'latin' => "A'ūdzu bikalimātillāhit-tāmmāti min syarri mā khalaq",
                'translation' => 'Aku berlindung dengan kalimat-kalimat Allah yang sempurna dari keburukan semua makhluk-Nya.',
                'source' => 'HR. Muslim no. 2708',
                'content_text' => "Dibaca 3x di pagi dan petang hari. Rasulullah bersabda: 'Barangsiapa singgah di suatu tempat lalu membaca doa ini, maka tidak ada yang membahayakannya hingga ia pergi dari tempat itu.' (HR. Muslim).",
                'sort_order' => 3,
            ],
            [
                'category' => 'hadits-pilihan',
                'title' => 'Doa Memohon Ilmu yang Bermanfaat',
                'arabic' => 'اللَّهُمَّ إِنِّي أَسْأَلُكَ عِلْمًا نَافِعًا وَرِزْقًا طَيِّبًا وَعَمَلًا مُتَقَبَّلًا',
                'latin' => "Allāhumma innī as'aluka 'ilman nāfi'an wa rizqan ṭhayyiban wa 'amalan mutaqabbalan",
                'translation' => 'Ya Allah, sesungguhnya aku memohon kepada-Mu ilmu yang bermanfaat, rezeki yang baik, dan amal yang diterima.',
                'source' => 'HR. Ibnu Majah no. 925',
                'content_text' => "Dibaca setelah salam dari shalat Subuh. Doa ini mencakup tiga hal penting yang dibutuhkan seorang muslim: ilmu yang bermanfaat untuk bekalnya, rezeki yang halal dan baik untuk kehidupannya, dan amal yang diterima oleh Allah.",
                'sort_order' => 4,
            ],
            [
                'category' => 'hadits-pilihan',
                'title' => 'Dzikir Subhanallah, Alhamdulillah, Allahu Akbar',
                'arabic' => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ سُبْحَانَ اللَّهِ الْعَظِيمِ',
                'latin' => "Subḥānallāhi wa biḥamdih, subḥānallāhil-'aẓīm",
                'translation' => 'Maha Suci Allah dan segala puji bagi-Nya, Maha Suci Allah Yang Maha Agung.',
                'source' => 'HR. Bukhari no. 6406, Muslim no. 2694',
                'content_text' => "Rasulullah shallallahu 'alaihi wa sallam bersabda: 'Dua kalimat yang ringan di lidah, berat di timbangan, dan dicintai oleh Ar-Rahman: Subhanallahi wa bihamdihi, Subhanallahil 'Azhim.'",
                'sort_order' => 5,
            ],
            [
                'category' => 'hadits-pilihan',
                'title' => 'Doa Mohon Kebaikan Dunia dan Akhirat',
                'arabic' => 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
                'latin' => "Rabbanā ātinā fid-dunyā ḥasanatan wa fil-ākhirati ḥasanatan wa qinā 'adzāban-nār",
                'translation' => 'Ya Tuhan kami, berilah kami kebaikan di dunia dan kebaikan di akhirat, dan lindungilah kami dari siksa api neraka.',
                'source' => 'QS. Al-Baqarah [2]: 201; HR. Bukhari no. 4522',
                'content_text' => "Ini adalah doa yang paling sering dibaca oleh Rasulullah shallallahu 'alaihi wa sallam. Anas bin Malik berkata: 'Doa yang paling sering dipanjatkan Nabi adalah: Allahumma Rabbana atina fid-dunya hasanatan wa fil-akhirati hasanatan wa qina 'adzaban-nar.' (HR. Bukhari & Muslim).",
                'sort_order' => 6,
            ],
        ];

        foreach ($duas as $d) {
            $catSlug = $d['category'];
            unset($d['category']);

            $category = DoaCategory::where('slug', $catSlug)->first();
            if (!$category) continue;

            $d['doa_category_id'] = $category->id;
            $d['slug'] = Str::slug($d['title']);
            $d['status'] = 'publish';

            if (!isset($d['content_html'])) $d['content_html'] = null;
            if (!isset($d['content_text'])) $d['content_text'] = null;

            $existing = Doa::where('slug', $d['slug'])->first();
            if ($existing) {
                $d['slug'] .= '-' . Str::random(4);
            }

            Doa::updateOrCreate(
                ['slug' => $d['slug']],
                $d
            );
        }
    }
}
