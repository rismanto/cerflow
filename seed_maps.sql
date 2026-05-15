INSERT INTO cer_maps (id, title) VALUES 
(1, 'Dampak Deforestasi pada Keanekaragaman Hayati'),
(2, 'Pemanasan Global dan Efek Rumah Kaca');

INSERT INTO triplets (map_id, claim, evidence, reasoning) VALUES 
-- Map 1 (3 Triplets)
(1, 'Deforestasi mengurangi populasi burung.', 'Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.', 'Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.'),
(1, 'Suhu udara lokal meningkat akibat penebangan hutan.', 'Data termometer menunjukkan kenaikan rata-rata 2°C di area yang baru saja ditebang.', 'Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.'),
(1, 'Kualitas air sungai di sekitar hutan memburuk.', 'Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.', 'Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.'),

-- Map 2 (5 Triplets)
(2, 'Suhu rata-rata bumi terus meningkat secara signifikan.', 'Data pengamatan satelit menunjukkan rekor suhu terpanas terjadi dalam satu dekade terakhir.', 'Gas rumah kaca memerangkap energi panas dari matahari di atmosfer bumi yang seharusnya dipantulkan kembali ke luar angkasa.'),
(2, 'Lapisan es di kutub mencair jauh lebih cepat dari sebelumnya.', 'Volume lapisan es di Kutub Utara tercatat berkurang sekitar 13% setiap dekade.', 'Peningkatan suhu global mempercepat titik lebur bongkahan es di area sekitar kutub.'),
(2, 'Permukaan air laut global terus mengalami kenaikan.', 'Pengukuran satelit mencatat kenaikan permukaan laut rata-rata 3.3 milimeter per tahun.', 'Pencairan es di daratan luas (seperti Greenland) secara konsisten menambah volume total air di lautan.'),
(2, 'Cuaca ekstrem menjadi jauh lebih sering terjadi belakangan ini.', 'Laporan pengamat iklim menyebutkan frekuensi badai dan kekeringan panjang meningkat 2x lipat.', 'Suhu yang lebih tinggi meningkatkan laju penguapan air, sehingga mengacaukan siklus hidrologi alami.'),
(2, 'Aktivitas manusia adalah penyebab utama tingginya gas rumah kaca.', 'Konsentrasi CO2 di atmosfer mencapai 420 ppm, level tertinggi dalam sejarah umat manusia.', 'Pembakaran bahan bakar fosil (batu bara, minyak) oleh manusia melepaskan karbon yang sebelumnya tersimpan di dalam bumi.');
