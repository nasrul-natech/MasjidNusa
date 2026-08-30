<?php

namespace Database\Seeders;

use App\Models\Doa;
use App\Models\DoaCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ZikirPagiPetangSeeder extends Seeder
{
    public function run(): void
    {
        $category = DoaCategory::where('slug', 'zikir-pagi-petang')->first();
        if (!$category) return;

        $duas = [
            [
                'title' => 'Membaca Ayat Kursi (Pagi & Petang)',
                'arabic' => 'اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ ۚ لَّهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ ۗ مَن ذَا الَّذِي يَشْفَعُ عِندَهُ إِلَّا بِإِذْنِهِ ۚ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ ۖ وَلَا يُحِيطُونَ بِشَيْءٍ مِّنْ عِلْمِهِ إِلَّا بِمَا شَاءَ ۚ وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالْأَرْضَ ۖ وَلَا يَئُودُهُ حِفْظُهُمَا ۚ وَهُوَ الْعَلِيُّ الْعَظِيمُ',
                'latin' => "Allāhu lā ilāha illā huwal-ḥayyul-qayyūm. Lā ta'khudzuhū sinatun walā naum. Lahū mā fis-samāwāti wa mā fil-arḍ. Man dzalladzī yasyfa'u 'indahū illā bi'idznih. Ya'lamu mā baina aidīhim wa mā khalfahum. Wa lā yuḥīṭūna bisyai'in min 'ilmihī illā bimā syā'. Wasi'a kursiyyuhus-samāwāti wal-arḍ. Wa lā ya'ūduhū ḥifẓuhumā wa huwal-'aliyyul-'aẓīm",
                'translation' => 'Allah, tiada tuhan selain Dia, Yang Maha Hidup lagi terus-menerus mengurus makhluk-Nya. Dia tidak mengantuk dan tidak tidur. Milik-Nya apa yang ada di langit dan apa yang ada di bumi. Tidak ada yang dapat memberi syafaat di sisi-Nya tanpa izin-Nya. Dia mengetahui apa yang ada di hadapan mereka dan apa yang ada di belakang mereka. Dan mereka tidak mengetahui sesuatu pun dari ilmu-Nya melainkan apa yang Dia kehendaki. Kursi-Nya meliputi langit dan bumi. Dan Dia tidak merasa berat memelihara keduanya. Dan Dia Maha Tinggi lagi Maha Agung.',
                'source' => "QS. Al-Baqarah [2]: 255",
                'content_text' => "Dibaca 1x setiap pagi dan petang. Barangsiapa membacanya ketika pagi, ia dijaga dari gangguan jin hingga petang. Barangsiapa membacanya ketika petang, ia dijaga hingga pagi (HR. Al-Hakim, dishahihkan Al-Albani).",
                'sort_order' => 1,
            ],
            [
                'title' => 'Membaca Surat Al-Ikhlas (3x Pagi & Petang)',
                'arabic' => 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ. قُلْ هُوَ اللَّهُ أَحَدٌ. اللَّهُ الصَّمَدُ. لَمْ يَلِدْ وَلَمْ يُولَدْ. وَلَمْ يَكُنْ لَّهُ كُفُوًا أَحَدٌ',
                'latin' => "Bismillāhir-raḥmānir-raḥīm. Qul huwallāhu aḥad. Allāhuṣ-ṣamad. Lam yalid wa lam yūlad. Wa lam yakun lahū kufuwan aḥad",
                'translation' => 'Dengan nama Allah Yang Maha Pengasih lagi Maha Penyayang. Katakanlah: Dialah Allah Yang Maha Esa. Allah tempat bergantung segala sesuatu. Dia tidak beranak dan tidak pula diperanakkan. Dan tidak ada sesuatu pun yang setara dengan-Nya.',
                'source' => "QS. Al-Ikhlas [112]: 1-4; HR. Abu Dawud no. 5082, At-Tirmidzi no. 3575",
                'content_text' => "Dibaca 3x setiap pagi dan petang. Rasulullah ﷺ bersabda: 'Bacalah Qul Huwallāhu Aḥad dan Al-Mu'awwidzatain (Al-Falaq & An-Naas) tiga kali setiap pagi dan petang, niscaya ia mencukupimu dari segala sesuatu.'",
                'sort_order' => 2,
            ],
            [
                'title' => 'Membaca Surat Al-Falaq (3x Pagi & Petang)',
                'arabic' => 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ. قُلْ أَعُوذُ بِرَبِّ الْفَلَقِ. مِن شَرِّ مَا خَلَقَ. وَمِن شَرِّ غَاسِقٍ إِذَا وَقَبَ. وَمِن شَرِّ النَّفَّاثَاتِ فِي الْعُقَدِ. وَمِن شَرِّ حَاسِدٍ إِذَا حَسَدَ',
                'latin' => "Bismillāhir-raḥmānir-raḥīm. Qul a'ūdzu birabbil-falaq. Min syarri mā khalaq. Wa min syarri ghāsiqin idzā waqab. Wa min syarrin-naffātsāti fil-'uqad. Wa min syarri ḥāsidin idzā ḥasad",
                'translation' => 'Dengan nama Allah Yang Maha Pengasih lagi Maha Penyayang. Katakanlah: Aku berlindung kepada Tuhan yang menguasai subuh (fajar). Dari keburukan makhluk-Nya. Dan dari keburukan malam apabila telah gelap gulita. Dan dari keburukan para peniup pada buhul-buhul (tukang sihir). Dan dari keburukan orang yang dengki apabila ia dengki.',
                'source' => "QS. Al-Falaq [113]: 1-5; HR. Abu Dawud no. 5082",
                'content_text' => "Dibaca 3x setiap pagi dan petang sebagai perlindungan dari segala keburukan.",
                'sort_order' => 3,
            ],
            [
                'title' => 'Membaca Surat An-Naas (3x Pagi & Petang)',
                'arabic' => 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ. قُلْ أَعُوذُ بِرَبِّ النَّاسِ. مَلِكِ النَّاسِ. إِلَٰهِ النَّاسِ. مِن شَرِّ الْوَسْوَاسِ الْخَنَّاسِ. الَّذِي يُوَسْوِسُ فِي صُدُورِ النَّاسِ. مِنَ الْجِنَّةِ وَالنَّاسِ',
                'latin' => "Bismillāhir-raḥmānir-raḥīm. Qul a'ūdzu birabbin-nās. Malikin-nās. Ilāhin-nās. Min syarril-waswāsil-khannās. Alladzī yuwaswisu fī ṣudūrin-nās. Minal-jinnati wan-nās",
                'translation' => 'Dengan nama Allah Yang Maha Pengasih lagi Maha Penyayang. Katakanlah: Aku berlindung kepada Tuhannya manusia. Rajanya manusia. Sesembahannya manusia. Dari keburukan (bisikan) setan yang biasa bersembunyi. Yang membisikkan (kejahatan) ke dalam dada manusia. Dari golongan jin dan manusia.',
                'source' => "QS. An-Naas [114]: 1-6; HR. Abu Dawud no. 5082",
                'content_text' => "Dibaca 3x setiap pagi dan petang bersama Al-Ikhlas dan Al-Falaq.",
                'sort_order' => 4,
            ],
            [
                'title' => 'Sayyidul Istighfar (Pagi & Petang)',
                'arabic' => 'اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَهَ إِلَّا أَنْتَ خَلَقْتَنِي وَأَنَا عَبْدُكَ وَأَنَا عَلَى عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ وَأَبُوءُ بِذَنْبِي فَاغْفِرْ لِي فَإِنَّهُ لَا يَغْفِرُ الذُّنُوبَ إِلَّا أَنْتَ',
                'latin' => "Allāhumma anta rabbī lā ilāha illā anta, khalaqtanī wa ana 'abduka, wa ana 'alā 'ahdika wa wa'dika mastaṭa'tu. A'ūdzu bika min syarri mā ṣana'tu. Abū'u laka bini'matika 'alayya wa abū'u bidzanbī faghfir lī fa innahū lā yaghfirudz-dzunūba illā ant",
                'translation' => 'Ya Allah, Engkau adalah Tuhanku, tiada tuhan selain Engkau. Engkau telah menciptakanku dan aku adalah hamba-Mu. Aku berada di atas perjanjian-Mu dan janji-Mu semampuku. Aku berlindung kepada-Mu dari keburukan apa yang aku perbuat. Aku mengakui nikmat-Mu kepadaku dan aku mengakui dosaku. Maka ampunilah aku, karena sesungguhnya tidak ada yang mengampuni dosa kecuali Engkau.',
                'source' => 'HR. Bukhari no. 6306',
                'content_text' => "Dibaca 1x pagi dan petang. Rasulullah ﷺ bersabda: 'Barangsiapa membacanya di siang hari dengan penuh keyakinan lalu mati pada hari itu sebelum petang, ia termasuk ahli surga. Barangsiapa membacanya di malam hari dengan penuh keyakinan lalu mati sebelum pagi, ia termasuk ahli surga.'",
                'sort_order' => 5,
            ],
            [
                'title' => "Bismillāhil-ladzī lā yaḍurru... (3x Pagi & Petang)",
                'arabic' => 'بِسْمِ اللَّهِ الَّذِي لَا يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الْأَرْضِ وَلَا فِي السَّمَاءِ وَهُوَ السَّمِيعُ الْعَلِيمُ',
                'latin' => "Bismillāhil-ladzī lā yaḍurru ma'asmihi syai'un fil-arḍi wa lā fis-samā'i wa huwas-samī'ul-'alīm",
                'translation' => 'Dengan nama Allah yang dengan nama-Nya tidak ada sesuatu pun yang membahayakan di bumi maupun di langit, dan Dia Maha Mendengar lagi Maha Mengetahui.',
                'source' => 'HR. Abu Dawud no. 5088, At-Tirmidzi no. 3388, Ibnu Majah no. 3869',
                'content_text' => "Dibaca 3x setiap pagi dan petang. Rasulullah ﷺ bersabda: 'Barangsiapa yang mengucapkan doa ini tiga kali di pagi hari dan tiga kali di petang hari, maka tidak ada sesuatu pun yang membahayakannya.' (HR. Abu Dawud & At-Tirmidzi, shahih)",
                'sort_order' => 6,
            ],
            [
                'title' => "Raḍītu billāhi rabba... (3x Pagi & Petang)",
                'arabic' => 'رَضِيتُ بِاللَّهِ رَبًّا وَبِالْإِسْلَامِ دِينًا وَبِمُحَمَّدٍ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ نَبِيًّا',
                'latin' => "Raḍītu billāhi rabbā, wa bil-islāmi dīnā, wa bi Muḥammadin ṣallallāhu 'alaihi wa sallama nabiyyā",
                'translation' => 'Aku ridha Allah sebagai Tuhanku, Islam sebagai agamaku, dan Muhammad ﷺ sebagai Nabiku.',
                'source' => 'HR. Abu Dawud no. 5072, At-Tirmidzi no. 3389, Ahmad',
                'content_text' => "Dibaca 3x setiap pagi dan petang. Rasulullah ﷺ bersabda: 'Barangsiapa yang mengucapkannya tiga kali setiap pagi dan petang, maka sudah menjadi hak Allah untuk meridhainya pada hari Kiamat.' (HR. Ahmad, dishahihkan Al-Albani)",
                'sort_order' => 7,
            ],
            [
                'title' => "A'ūdzu bikalimatillāhit-tāmmāti... (Petang)",
                'arabic' => 'أَعُوذُ بِكَلِمَاتِ اللَّهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ',
                'latin' => "A'ūdzu bikalimātillāhit-tāmmāti min syarri mā khalaq",
                'translation' => 'Aku berlindung dengan kalimat-kalimat Allah yang sempurna dari keburukan semua makhluk-Nya.',
                'source' => 'HR. Muslim no. 2708',
                'content_text' => "Dibaca 3x di petang hari. Rasulullah ﷺ bersabda: 'Barangsiapa mengucapkan doa ini tiga kali di petang hari, maka sengatan (binatang berbisa) tidak akan membahayakannya pada malam itu.' (HR. At-Tirmidzi no. 3604, shahih). Suhail (perawi) berkata: Keluarga kami mempelajarinya dan membacakannya setiap petang.",
                'sort_order' => 8,
            ],
            [
                'title' => "Subḥānallāhi wa biḥamdih (100x)",
                'arabic' => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ',
                'latin' => "Subḥānallāhi wa biḥamdih",
                'translation' => 'Maha Suci Allah dan segala puji bagi-Nya.',
                'source' => 'HR. Bukhari no. 6405, Muslim no. 2692',
                'content_text' => "Dibaca 100x setiap pagi dan petang. Rasulullah ﷺ bersabda: 'Barangsiapa mengucapkan Subhanallahi wa bihamdihi seratus kali sehari, maka dosa-dosanya akan dihapuskan sekalipun sebanyak buih lautan.' (HR. Bukhari & Muslim)",
                'sort_order' => 9,
            ],
            [
                'title' => "Lā ilāha illallāh waḥdahu... (10x/100x Pagi)",
                'arabic' => 'لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ',
                'latin' => "Lā ilāha illallāhu waḥdahu lā syarīka lah, lahul-mulku wa lahul-ḥamdu wa huwa 'alā kulli syai'in qadīr",
                'translation' => 'Tiada tuhan selain Allah semata, tiada sekutu bagi-Nya. Milik-Nya kerajaan dan segala pujian, dan Dia Maha Kuasa atas segala sesuatu.',
                'source' => 'HR. Bukhari no. 3293, Muslim no. 2693',
                'content_text' => "Dibaca 10x di pagi hari (atau 100x). Rasulullah ﷺ bersabda: 'Barangsiapa mengucapkannya 10x di pagi hari, ditulis baginya 10 kebaikan, dihapus 10 keburukan, diangkat 10 derajat, ia terlindung dari setan hingga petang, dan tidak ada yang datang pada hari Kiamat dengan amalan yang lebih baik darinya kecuali orang yang mengucapkannya lebih banyak.' (HR. Ahmad, dishahihkan Al-Albani)",
                'sort_order' => 10,
            ],
            [
                'title' => "Allāhumma innī aṣbaḥtu... (Pagi)",
                'arabic' => 'اللَّهُمَّ إِنِّي أَصْبَحْتُ أُشْهِدُكَ وَأُشْهِدُ حَمَلَةَ عَرْشِكَ وَمَلَائِكَتَكَ وَجَمِيعَ خَلْقِكَ أَنَّكَ أَنْتَ اللَّهُ لَا إِلَهَ إِلَّا أَنْتَ وَحْدَكَ لَا شَرِيكَ لَكَ وَأَنَّ مُحَمَّدًا عَبْدُكَ وَرَسُولُكَ',
                'latin' => "Allāhumma innī aṣbaḥtu usyhiduka wa usyhidu ḥamalata 'arsyika wa malā'ikataka wa jamī'a khalqika annaka antallāhu lā ilāha illā anta waḥdaka lā syarīka laka wa anna Muḥammadan 'abduka wa rasūluk",
                'translation' => 'Ya Allah, sesungguhnya aku di waktu pagi ini mempersaksikan Engkau, para malaikat pemikul Arsy-Mu, para malaikat-Mu, dan seluruh makhluk-Mu bahwa sesungguhnya Engkau adalah Allah, tiada tuhan selain Engkau semata, tiada sekutu bagi-Mu, dan sesungguhnya Muhammad adalah hamba dan utusan-Mu.',
                'source' => 'HR. Abu Dawud no. 5069, An-Nasa\'i dalam \'Amalul Yaum wal Lailah no. 9',
                'content_text' => "Dibaca 4x di pagi hari (ganti 'aṣbaḥtu' dengan 'amsaitu' untuk petang). Rasulullah ﷺ bersabda: 'Barangsiapa mengucapkannya 4x di pagi atau petang hari, maka Allah akan membebaskannya dari api neraka.' (HR. Abu Dawud, dishahihkan Al-Albani)",
                'sort_order' => 11,
            ],
            [
                'title' => "Allāhumma 'āfinī fī badanī... (3x Pagi & Petang)",
                'arabic' => 'اللَّهُمَّ عَافِنِي فِي بَدَنِي، اللَّهُمَّ عَافِنِي فِي سَمْعِي، اللَّهُمَّ عَافِنِي فِي بَصَرِي، لَا إِلَهَ إِلَّا أَنْتَ. اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْكُفْرِ وَالْفَقْرِ، وَأَعُوذُ بِكَ مِنْ عَذَابِ الْقَبْرِ، لَا إِلَهَ إِلَّا أَنْتَ',
                'latin' => "Allāhumma 'āfinī fī badanī. Allāhumma 'āfinī fī sam'ī. Allāhumma 'āfinī fī baṣarī. Lā ilāha illā ant. Allāhumma innī a'ūdzu bika minal-kufri wal-faqri. Wa a'ūdzu bika min 'adzābil-qabr. Lā ilāha illā ant",
                'translation' => 'Ya Allah, selamatkanlah tubuhku. Ya Allah, selamatkanlah pendengaranku. Ya Allah, selamatkanlah penglihatanku. Tiada tuhan selain Engkau. Ya Allah, sesungguhnya aku berlindung kepada-Mu dari kekufuran dan kefakiran, dan aku berlindung kepada-Mu dari azab kubur. Tiada tuhan selain Engkau.',
                'source' => 'HR. Abu Dawud no. 5090',
                'content_text' => "Dibaca 3x setiap pagi dan petang. Doa ini memohon keselamatan tiga anggota badan yang paling penting serta berlindung dari kekufuran, kemiskinan, dan siksa kubur.",
                'sort_order' => 12,
            ],
            [
                'title' => "Ḥasbiyallāhu lā ilāha illā huwa... (7x Pagi & Petang)",
                'arabic' => 'حَسْبِيَ اللَّهُ لَا إِلَهَ إِلَّا هُوَ عَلَيْهِ تَوَكَّلْتُ وَهُوَ رَبُّ الْعَرْشِ الْعَظِيمِ',
                'latin' => "Ḥasbiyallāhu lā ilāha illā huwa 'alaihi tawakkaltu wa huwa rabbul-'arsyil-'aẓīm",
                'translation' => 'Cukuplah Allah bagiku, tiada tuhan selain Dia. Hanya kepada-Nya aku bertawakkal dan Dia adalah Tuhan Arsy yang agung.',
                'source' => "QS. At-Taubah [9]: 129; HR. Abu Dawud no. 5081",
                'content_text' => "Dibaca 7x setiap pagi dan petang. Rasulullah ﷺ bersabda: 'Barangsiapa mengucapkannya 7x setiap pagi dan petang, maka Allah akan mencukupinya dari segala yang mengkhawatirkannya dari urusan dunia dan akhirat.' (HR. Abu Dawud, Ibnu As-Sunni. Sanad hadits ini dinyatakan hasan oleh Syu'aib Al-Arnauth)",
                'sort_order' => 13,
            ],
            [
                'title' => "Aṣbaḥnā wa aṣbaḥal-mulku lillāh... (Pagi)",
                'arabic' => 'أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ وَالْحَمْدُ لِلَّهِ، لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ. رَبِّ أَسْأَلُكَ خَيْرَ مَا فِي هَذَا الْيَوْمِ وَخَيْرَ مَا بَعْدَهُ، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِي هَذَا الْيَوْمِ وَشَرِّ مَا بَعْدَهُ. رَبِّ أَعُوذُ بِكَ مِنَ الْكَسَلِ وَسُوءِ الْكِبَرِ. رَبِّ أَعُوذُ بِكَ مِنْ عَذَابٍ فِي النَّارِ وَعَذَابٍ فِي الْقَبْرِ',
                'latin' => "Aṣbaḥnā wa aṣbaḥal-mulku lillāh, wal-ḥamdu lillāh. Lā ilāha illallāhu waḥdahu lā syarīka lah, lahul-mulku wa lahul-ḥamdu wa huwa 'alā kulli syai'in qadīr. Rabbī as'aluka khaira mā fī hādzal-yaum wa khaira mā ba'dah. Wa a'ūdzu bika min syarri mā fī hādzal-yaum wa syarri mā ba'dah. Rabbī a'ūdzu bika minal-kasali wa sū'il-kibar. Rabbī a'ūdzu bika min 'adzābin fin-nāri wa 'adzābin fil-qabr",
                'translation' => 'Kami telah memasuki waktu pagi dan kerajaan hanya milik Allah, segala puji bagi Allah. Tiada tuhan selain Allah semata, tiada sekutu bagi-Nya, milik-Nya kerajaan dan segala pujian, dan Dia Maha Kuasa atas segala sesuatu. Wahai Tuhanku, aku memohon kepada-Mu kebaikan yang ada pada hari ini dan kebaikan sesudahnya. Dan aku berlindung kepada-Mu dari keburukan hari ini dan keburukan sesudahnya. Wahai Tuhanku, aku berlindung kepada-Mu dari kemalasan dan keburukan usia tua. Wahai Tuhanku, aku berlindung kepada-Mu dari siksa api neraka dan siksa kubur.',
                'source' => 'HR. Muslim no. 2723',
                'content_text' => "Dibaca 1x di pagi hari (ganti 'aṣbaḥnā' dengan 'amsainā' dan 'hādzal-yaum' dengan 'hādzihil-lailah' untuk petang).",
                'sort_order' => 14,
            ],
            [
                'title' => "Allāhumma innī as'alukal-'afwa wal-'āfiyah... (Pagi & Petang)",
                'arabic' => 'اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي الدُّنْيَا وَالْآخِرَةِ، اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي دِينِي وَدُنْيَايَ وَأَهْلِي وَمَالِي، اللَّهُمَّ اسْتُرْ عَوْرَاتِي وَآمِنْ رَوْعَاتِي، اللَّهُمَّ احْفَظْنِي مِنْ بَيْنِ يَدَيَّ وَمِنْ خَلْفِي وَعَنْ يَمِينِي وَعَنْ شِمَالِي وَمِنْ فَوْقِي، وَأَعُوذُ بِعَظَمَتِكَ أَنْ أُغْتَالَ مِنْ تَحْتِي',
                'latin' => "Allāhumma innī as'alukal-'afwa wal-'āfiyata fid-dunyā wal-ākhirah. Allāhumma innī as'alukal-'afwa wal-'āfiyata fī dīnī wa dunyāya wa ahlī wa mālī. Allāhummastur 'awrātī wa āmin raw'ātī. Allāhummaḥfaẓnī min baini yadayya wa min khalfī wa 'an yamīnī wa 'an syimālī wa min fawqī. Wa a'ūdzu bi'aẓamatika an ughtāla min taḥtī",
                'translation' => 'Ya Allah, sesungguhnya aku memohon ampunan dan keselamatan di dunia dan akhirat. Ya Allah, aku memohon ampunan dan keselamatan dalam agamaku, duniaku, keluargaku, dan hartaku. Ya Allah, tutupilah auratku (aibku) dan amankanlah aku dari ketakutan. Ya Allah, jagalah aku dari depan, belakang, kanan, kiri, dan atasku. Dan aku berlindung dengan keagungan-Mu dari dibinasakan dari bawahku.',
                'source' => 'HR. Abu Dawud no. 5074, Ibnu Majah no. 3871',
                'content_text' => "Dibaca 1x setiap pagi dan petang. Doa ini sangat komprehensif karena memohon keselamatan dari segala arah dan dalam semua aspek kehidupan.",
                'sort_order' => 15,
            ],
        ];

        foreach ($duas as $d) {
            $d['doa_category_id'] = $category->id;
            $d['slug'] = Str::slug($d['title']);
            $d['status'] = 'publish';

            if (!isset($d['content_html'])) $d['content_html'] = null;
            if (!isset($d['content_text'])) $d['content_text'] = $d['content_text'] ?? null;

            $existing = Doa::where('slug', $d['slug'])->first();
            if ($existing) {
                $d['slug'] .= '-' . Str::random(4);
            }

            Doa::updateOrCreate(['slug' => $d['slug']], $d);
        }
    }
}
