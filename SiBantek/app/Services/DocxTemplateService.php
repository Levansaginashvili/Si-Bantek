<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use ZipArchive;

class DocxTemplateService
{
    private const NS_W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * @param  array<string, mixed>  $sekolah
     * @param  array<string, mixed>|null  $rab
     */
    public function fill(string $templatePath, string $docType, array $sekolah, ?array $rab = null): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'sibantek_').'.docx';
        copy($templatePath, $tempPath);

        $zip = new ZipArchive;
        if ($zip->open($tempPath) !== true) {
            throw new \RuntimeException('Gagal membuka template DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if (! $xml) {
            $zip->close();
            throw new \RuntimeException('Template DOCX tidak valid.');
        }

        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml);

        $this->processDocument($dom, $docType, $sekolah, $rab);

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        return $tempPath;
    }

    /**
     * Process document based on docType.
     *
     * @param  array<string, mixed>  $s
     * @param  array<string, mixed>|null  $rab
     */
    private function processDocument(DOMDocument $dom, string $type, array $s, ?array $rab): void
    {
        $tanggal = $this->tanggalIndonesia();
        $kabupaten = $s['kabupaten'] ?? '';
        $namaSekolah = $s['nama_sekolah'] ?? '';
        $namaKepsek = $s['nama_kepsek'] ?? '';
        $nipKepsek = $s['nip_kepsek'] ?? '';
        $alamat = $s['alamat'] ?? '';
        $namaBendahara = $s['nama_bendahara'] ?? '';
        $nipBendahara = $s['nip_bendahara'] ?? '';

        $context = [
            'npsn' => $s['npsn'] ?? '',
            'nama_sekolah' => $namaSekolah,
            'nama_kepsek' => $namaKepsek,
            'nip_kepsek' => $nipKepsek,
            'alamat' => $alamat,
            'rt' => $s['rt'] ?? '',
            'rw' => $s['rw'] ?? '',
            'nomor_bangunan' => $s['nomor_bangunan'] ?? '',
            'desa_kelurahan' => $s['desa_kelurahan'] ?? '',
            'kecamatan' => $s['kecamatan'] ?? '',
            'kabupaten' => $kabupaten,
            'provinsi' => $s['provinsi'] ?? '',
            'kode_pos' => $s['kode_pos'] ?? '',
            'tanggal' => $tanggal,
            'nama_bendahara' => $namaBendahara,
            'nip_bendahara' => $nipBendahara,
            'nama_bank' => $s['nama_bank'] ?? '',
            'nomor_rekening' => $s['nomor_rekening'] ?? '',
            'atas_nama_rekening' => $s['atas_nama_rekening'] ?? '',
            'no_telepon' => $s['no_telepon'] ?? '',
            'email_sekolah' => $s['email_sekolah'] ?? '',
            'nama_ketua_komite' => $s['nama_ketua_komite'] ?? '',
            'nama_ppk' => $s['nama_ppk'] ?? 'Hendro Sucipto, S.Kom.',
            'nip_ppk' => $s['nip_ppk'] ?? '197803152003121002',
            'jabatan_ppk' => $s['jabatan_ppk'] ?? 'Pejabat Pembuat Komitmen Direktorat SMP',
            'alamat_ppk' => $s['alamat_ppk'] ?? 'Kompleks Kemendikbudristek Gedung E Lt. 17, Jl. Jenderal Sudirman, Senayan, Jakarta Pusat',
        ];

        if ($type === 'sptjm') {
            $this->fillSptjm($dom, $context);
        } elseif ($type === 'pakta_integritas') {
            $this->fillPaktaIntegritas($dom, $context);
        } elseif ($type === 'rab') {
            $this->fillRab($dom, $context, $rab);
        } elseif ($type === 'laporan_awal') {
            $this->fillLaporanAwal($dom, $context, $rab);
        } elseif ($type === 'laporan_akhir') {
            $this->fillLaporanAkhir($dom, $context);
        } elseif ($type === 'bast') {
            $this->fillBast($dom, $context, $rab);
        } elseif ($type === 'pks') {
            $this->fillPks($dom, $context, $rab);
        } elseif ($type === 'buku_inventaris') {
            $this->fillBukuInventaris($dom, $context, $rab);
        } elseif ($type === 'pengantar_lpj') {
            $this->fillPengantarLaporan($dom, $context);
        } elseif ($type === 'lpj') {
            $this->fillLpj($dom, $context, $rab);
        } elseif ($type === 'perbandingan_siplah') {
            $this->fillPerbandinganProduk($dom, $context);
        }
    }

    /**
     * Specific auto-fill logic for SPTJM.docx.
     *
     * @param  array<string, string>  $data
     */
    private function fillSptjm(DOMDocument $dom, array $data): void
    {
        $paragraphs = iterator_to_array($dom->getElementsByTagNameNS(self::NS_W, 'p'));

        foreach ($paragraphs as $p) {
            $fullText = $this->getDirectRunsText($p);
            $clean = trim(preg_replace('/\s+/', ' ', $fullText));

            // 1. KOP SATUAN
            if (str_contains($clean, 'KOP LEMBAGA SATUAN SMP')) {
                $this->replaceDirectRunsText($p, 'KOP LEMBAGA SATUAN SMP', 'KOP '.mb_strtoupper($data['nama_sekolah']));

                continue;
            }

            // 2. Yang bertanda tangan di bawah ini, saya :
            if (str_contains($clean, 'Yang bertanda tangan di bawah ini') && str_contains($clean, 'Nama')) {
                $this->splitYangBertandaTanganDanNama($p, $data['nama_kepsek']);

                continue;
            }

            // 3. NIP/NIK :
            if ($clean === 'NIP/NIK :' || $clean === 'NIP/NIK:') {
                $this->removeRightIndentation($p);
                $this->appendDirectRunValue($p, $data['nip_kepsek']);

                continue;
            }

            // 4. Jabatan :
            if ($clean === 'Jabatan :' || $clean === 'Jabatan:') {
                $this->removeRightIndentation($p);
                $this->appendDirectRunValue($p, 'Kepala Sekolah');

                continue;
            }

            // 5. Alamat :
            if ($clean === 'Alamat :' || $clean === 'Alamat:') {
                $this->removeRightIndentation($p);
                $this->appendDirectRunValue($p, $data['alamat']);

                continue;
            }

            // 6. Dibuat di :
            if ($clean === 'Dibuat di :' || $clean === 'Dibuat di:') {
                $this->removeRightIndentation($p);
                $this->appendDirectRunValue($p, $data['kabupaten']);

                continue;
            }

            // 7. Tanggal :
            if ($clean === 'Tanggal :' || $clean === 'Tanggal:') {
                $this->removeRightIndentation($p);
                $this->appendDirectRunValue($p, $data['tanggal']);

                continue;
            }

            // 8. Kepala Sekolah Menengah Pertama XXXX
            if (str_contains($clean, 'Kepala Sekolah Menengah Pertama XXXX')) {
                $this->removeRightIndentation($p);
                $this->splitIntoTwoParagraphs($p, 'Kepala Sekolah Menengah Pertama', $data['nama_sekolah']);

                continue;
            }

            // 9. Signature Block: "Nama Kepala NIP"
            if (str_contains($clean, 'Nama') && str_contains($clean, 'Kepala') && str_contains($clean, 'NIP') && ! str_contains($clean, 'Yang bertanda')) {
                $this->removeRightIndentation($p);
                $nipStr = $data['nip_kepsek'] !== '' ? 'NIP. '.$data['nip_kepsek'] : '';
                $this->splitSignatureIntoTwoParagraphs($p, $data['nama_kepsek'], $nipStr);

                continue;
            }
        }
    }

    /**
     * Specific auto-fill logic for PaktaIntegritas.docx.
     *
     * @param  array<string, string>  $data
     */
    private function fillPaktaIntegritas(DOMDocument $dom, array $data): void
    {
        $ns = self::NS_W;
        $paragraphs = iterator_to_array($dom->getElementsByTagNameNS($ns, 'p'));

        foreach ($paragraphs as $p) {
            $tNodes = $p->getElementsByTagNameNS($ns, 't');
            $txts = [];
            foreach ($tNodes as $t) {
                $txts[] = $t->textContent;
            }
            $full = trim(implode('', $txts));

            // 1. KOP SATUAN
            if (str_contains($full, 'KOP LEMBAGA SATUAN SMP')) {
                foreach ($tNodes as $idx => $t) {
                    if ($idx === 0) {
                        $t->textContent = 'KOP '.mb_strtoupper($data['nama_sekolah']);
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 2. Nama
            if (str_starts_with($full, 'Nama') && (str_contains($full, '…') || str_contains($full, '...'))) {
                $this->removeRightIndentation($p);
                foreach ($tNodes as $t) {
                    if (str_contains($t->textContent, '…') || str_contains($t->textContent, '...')) {
                        $t->textContent = ' '.$data['nama_kepsek'];
                    }
                }

                continue;
            }

            // 3. Jabatan
            if (str_starts_with($full, 'Jabatan')) {
                $this->removeRightIndentation($p);

                continue;
            }

            // 4. Alamat Satuan SMP
            if (str_starts_with($full, 'Alamat Satuan SMP') && (str_contains($full, '…') || str_contains($full, '...'))) {
                $this->removeRightIndentation($p);
                foreach ($tNodes as $t) {
                    if (str_contains($t->textContent, '…') || str_contains($t->textContent, '...')) {
                        $t->textContent = ' '.$data['alamat'];
                    }
                }

                continue;
            }

            // 5. Titimangsa
            if (str_contains($full, '2026') && (str_contains($full, '…') || str_contains($full, '...')) && ! str_contains($full, 'Bantuan Pemerintah') && ! str_contains($full, 'Peralatan TIK')) {
                foreach ($tNodes as $idx => $t) {
                    if ($idx === 0) {
                        $t->textContent = $data['kabupaten'].', '.$data['tanggal'];
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 6. (nama jelas)
            if (str_contains($full, '(nama jelas)') || str_contains($full, '(nama') || $full === '(nama jelas)') {
                $rNodes = [];
                foreach ($p->childNodes as $child) {
                    if ($child->localName === 'r') {
                        $rNodes[] = $child;
                    }
                }
                $rPr = null;
                if (! empty($rNodes)) {
                    foreach ($rNodes[0]->childNodes as $c) {
                        if ($c->localName === 'rPr') {
                            $rPr = $c;
                        }
                    }
                }
                foreach ($rNodes as $r) {
                    $p->removeChild($r);
                }

                $rName = $dom->createElementNS($ns, 'w:r');
                if ($rPr) {
                    $rName->appendChild($rPr->cloneNode(true));
                }
                $tName = $dom->createElementNS($ns, 'w:t');
                $tName->setAttribute('xml:space', 'preserve');
                $tName->textContent = $data['nama_kepsek'];
                $rName->appendChild($tName);
                $p->appendChild($rName);

                if ($data['nip_kepsek'] !== '') {
                    $pPr = null;
                    foreach ($p->childNodes as $c) {
                        if ($c->localName === 'pPr') {
                            $pPr = $c;
                        }
                    }
                    $pNip = $dom->createElementNS($ns, 'w:p');
                    if ($pPr) {
                        $pNip->appendChild($pPr->cloneNode(true));
                    }

                    $rNip = $dom->createElementNS($ns, 'w:r');
                    if ($rPr) {
                        $rNip->appendChild($rPr->cloneNode(true));
                    }
                    $tNip = $dom->createElementNS($ns, 'w:t');
                    $tNip->setAttribute('xml:space', 'preserve');
                    $tNip->textContent = 'NIP. '.$data['nip_kepsek'];
                    $rNip->appendChild($tNip);
                    $pNip->appendChild($rNip);

                    if ($p->nextSibling) {
                        $p->parentNode->insertBefore($pNip, $p->nextSibling);
                    } else {
                        $p->parentNode->appendChild($pNip);
                    }
                }

                continue;
            }
        }
    }

    /**
     * Specific auto-fill logic for RAB.docx.
     *
     * @param  array<string, string>  $data
     * @param  array<string, mixed>|null  $rab
     */
    /**
     * Specific auto-fill logic for RAB.docx.
     *
     * @param  array<string, string>  $data
     * @param  array<string, mixed>|null  $rab
     */
    private function fillRab(DOMDocument $dom, array $data, ?array $rab): void
    {
        $ns = self::NS_W;

        // 1. Header KOP (direct body paragraphs only)
        $body = $dom->getElementsByTagNameNS($ns, 'body')->item(0);
        if ($body) {
            foreach ($body->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'p') {
                    $txt = trim($child->textContent);

                    if (str_contains($txt, 'KOP LEMBAGA SATUAN SMP')) {
                        $tNodes = $child->getElementsByTagNameNS($ns, 't');
                        foreach ($tNodes as $idx => $t) {
                            if ($idx === 0) {
                                $t->textContent = 'KOP '.mb_strtoupper($data['nama_sekolah']);
                            } else {
                                $t->textContent = '';
                            }
                        }
                    }
                }
            }
        }

        // 2. Table 0 Auto-fill
        $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
        if ($tables->length === 0) {
            return;
        }

        $tbl = $tables->item(0);
        $rows = $tbl->getElementsByTagNameNS($ns, 'tr');
        if ($rows->length < 8) {
            return;
        }

        // 2. Adjust Table 0 Grid and Column Widths so Satuan Barang fits 2 lines and Right Signature Block stays on the right
        $tblGrid = $tbl->getElementsByTagNameNS($ns, 'tblGrid')->item(0);
        if ($tblGrid) {
            $gridCols = $tblGrid->getElementsByTagNameNS($ns, 'gridCol');
            $colWidths = [476, 600, 2307, 1700, 259, 1365, 2288, 11];
            foreach ($gridCols as $idx => $gc) {
                if (isset($colWidths[$idx])) {
                    $gc->setAttributeNS($ns, 'w:w', (string) $colWidths[$idx]);
                }
            }
        }

        // Adjust Cell Widths for Rows 0 to 4
        for ($r = 0; $r <= 4; $r++) {
            $rRow = $rows->item($r);
            if ($rRow) {
                $cells = $rRow->getElementsByTagNameNS($ns, 'tc');
                $tcWs = [1076, 2307, 1700, 1624, 2299];
                foreach ($cells as $cIdx => $cell) {
                    $wNode = $cell->getElementsByTagNameNS($ns, 'tcW')->item(0);
                    if ($wNode && isset($tcWs[$cIdx])) {
                        $wNode->setAttributeNS($ns, 'w:w', (string) $tcWs[$cIdx]);
                    }
                }
            }
        }

        // Rows 1 to 4: Items
        if ($rab) {
            $items = ! empty($rab['items']) && is_array($rab['items']) ? $rab['items'] : [
                [
                    'merek_tipe_laptop' => $rab['merek_tipe_laptop'] ?? '',
                    'spesifikasi_ringkas' => $rab['spesifikasi_ringkas'] ?? '',
                    'jumlah_unit' => $rab['jumlah_unit'] ?? 8,
                    'harga_satuan' => $rab['harga_satuan'] ?? 0,
                    'total_harga' => $rab['total_harga'] ?? 0,
                ],
            ];

            for ($r = 1; $r <= 4; $r++) {
                $itemIndex = $r - 1;
                $rowItem = $rows->item($r);
                if (! $rowItem) {
                    continue;
                }
                $cells = $rowItem->getElementsByTagNameNS($ns, 'tc');
                if ($cells->length < 5) {
                    continue;
                }

                if (isset($items[$itemIndex])) {
                    $it = $items[$itemIndex];
                    $this->setTableCellText($dom, $cells->item(0), (string) $r, 'center', false, 20);
                    $itemDesc = ($it['merek_tipe_laptop'] ?? '');
                    if (! empty($it['spesifikasi_ringkas'])) {
                        $itemDesc .= "\n(".($it['spesifikasi_ringkas']).')';
                    }
                    $this->setTableCellText($dom, $cells->item(1), $itemDesc, 'left', false, 19);
                    $this->setTableCellText($dom, $cells->item(2), ($it['jumlah_unit'] ?? 0).' Unit', 'center', false, 20);

                    $hargaFmt = 'Rp'."\u{00A0}".number_format((float) ($it['harga_satuan'] ?? 0), 0, ',', '.');
                    $subTotal = isset($it['total_harga']) && $it['total_harga'] > 0
                        ? (float) $it['total_harga']
                        : ((int) ($it['jumlah_unit'] ?? 0) * (float) ($it['harga_satuan'] ?? 0));
                    $totalFmt = 'Rp'."\u{00A0}".number_format($subTotal, 0, ',', '.');

                    $this->setTableCellText($dom, $cells->item(3), $hargaFmt, 'center', false, 18);
                    $this->setTableCellText($dom, $cells->item(4), $totalFmt, 'center', false, 18);
                } else {
                    for ($c = 0; $c < 5; $c++) {
                        $this->setTableCellText($dom, $cells->item($c), '', 'center', false, 20);
                    }
                }
            }

            // Row 5: Total Bantuan
            $cells5 = $rows->item(5)->getElementsByTagNameNS($ns, 'tc');
            if ($cells5->length >= 4) {
                $tcWs5 = [1076, 4007, 1624, 2299];
                foreach ($cells5 as $cIdx => $cell) {
                    $wNode = $cell->getElementsByTagNameNS($ns, 'tcW')->item(0);
                    if ($wNode && isset($tcWs5[$cIdx])) {
                        $wNode->setAttributeNS($ns, 'w:w', (string) $tcWs5[$cIdx]);
                    }
                }
                $totalFmt = 'Rp'."\u{00A0}".number_format((float) ($rab['total_harga'] ?? 0), 0, ',', '.');
                $this->setTableCellText($dom, $cells5->item(3), $totalFmt, 'center', true, 18);
            }
        }

        // Balance Row 6 and Row 7 cell widths keeping original grid positions (Left=gridSpan 4, Right=gridSpan 2)
        foreach ([6, 7] as $rIdx) {
            $row = $rows->item($rIdx);
            $leftTc = $row->getElementsByTagNameNS($ns, 'tc')->item(0);
            $rightTc = $row->getElementsByTagNameNS($ns, 'tc')->item(1);

            $leftTcW = $leftTc->getElementsByTagNameNS($ns, 'tcW')->item(0);
            if ($leftTcW) {
                $leftTcW->setAttributeNS($ns, 'w:w', '4866');
            }
            $leftGridSpan = $leftTc->getElementsByTagNameNS($ns, 'gridSpan')->item(0);
            if ($leftGridSpan) {
                $leftGridSpan->setAttributeNS($ns, 'w:val', '4');
            }

            $rightTcW = $rightTc->getElementsByTagNameNS($ns, 'tcW')->item(0);
            if ($rightTcW) {
                $rightTcW->setAttributeNS($ns, 'w:w', '3653');
            }
            $rightGridSpan = $rightTc->getElementsByTagNameNS($ns, 'gridSpan')->item(0);
            if ($rightGridSpan) {
                $rightGridSpan->setAttributeNS($ns, 'w:val', '2');
            }
        }

        // Row 6 Left: Nama Sekolah
        $row6Left = $rows->item(6)->getElementsByTagNameNS($ns, 'tc')->item(0);
        if ($row6Left) {
            $this->removeRightIndentsFromNode($row6Left);
            $r6LeftPs = $row6Left->getElementsByTagNameNS($ns, 'p');
            foreach ($r6LeftPs as $p) {
                if (str_contains($p->textContent, 'Nama Sekolah')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = $data['nama_sekolah'];
                        } else {
                            $t->textContent = '';
                        }
                    }
                }
            }
        }

        // Row 6 Right: Titimangsa & Ketua Pelaksana
        $row6Right = $rows->item(6)->getElementsByTagNameNS($ns, 'tc')->item(1);
        if ($row6Right) {
            $this->removeRightIndentsFromNode($row6Right);
            $r6RightPs = $row6Right->getElementsByTagNameNS($ns, 'p');
            foreach ($r6RightPs as $p) {
                if (str_contains($p->textContent, '2026') || str_contains($p->textContent, 'Pelaksana')) {
                    $runs = [];
                    foreach ($p->childNodes as $c) {
                        if ($c->localName === 'r') {
                            $runs[] = $c;
                        }
                    }
                    foreach ($runs as $r) {
                        $p->removeChild($r);
                    }

                    // Set center alignment
                    $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                    if (! $pPr) {
                        $pPr = $dom->createElementNS($ns, 'w:pPr');
                        $p->insertBefore($pPr, $p->firstChild);
                    }
                    $jc = $pPr->getElementsByTagNameNS($ns, 'jc')->item(0);
                    if (! $jc) {
                        $jc = $dom->createElementNS($ns, 'w:jc');
                        $pPr->appendChild($jc);
                    }
                    $jc->setAttributeNS($ns, 'w:val', 'center');

                    $rDate = $dom->createElementNS($ns, 'w:r');
                    $rPr = $dom->createElementNS($ns, 'w:rPr');
                    $sz = $dom->createElementNS($ns, 'w:sz');
                    $sz->setAttributeNS($ns, 'w:val', '20');
                    $rPr->appendChild($sz);
                    $rDate->appendChild($rPr);

                    $tDate = $dom->createElementNS($ns, 'w:t');
                    $tDate->textContent = $data['kabupaten'].', '.$data['tanggal'];
                    $rDate->appendChild($tDate);
                    $p->appendChild($rDate);

                    $rBr = $dom->createElementNS($ns, 'w:r');
                    $br = $dom->createElementNS($ns, 'w:br');
                    $rBr->appendChild($br);
                    $p->appendChild($rBr);

                    $rKetua = $dom->createElementNS($ns, 'w:r');
                    $rPr2 = $dom->createElementNS($ns, 'w:rPr');
                    $sz2 = $dom->createElementNS($ns, 'w:sz');
                    $sz2->setAttributeNS($ns, 'w:val', '20');
                    $rPr2->appendChild($sz2);
                    $rKetua->appendChild($rPr2);

                    $tKetua = $dom->createElementNS($ns, 'w:t');
                    $tKetua->textContent = 'Ketua Pelaksana';
                    $rKetua->appendChild($tKetua);
                    $p->appendChild($rKetua);
                }
            }
        }

        // Row 7 Left: Kepsek
        $row7Left = $rows->item(7)->getElementsByTagNameNS($ns, 'tc')->item(0);
        if ($row7Left) {
            $this->removeRightIndentsFromNode($row7Left);
            $r7LeftPs = $row7Left->getElementsByTagNameNS($ns, 'p');
            foreach ($r7LeftPs as $p) {
                if (str_contains($p->textContent, '(Nama Jelas)')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = $data['nama_kepsek'];
                        } else {
                            $t->textContent = '';
                        }
                    }
                } elseif (str_starts_with(trim($p->textContent), 'NIP.')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'NIP. '.($data['nip_kepsek'] ?: '....................');
                        } else {
                            $t->textContent = '';
                        }
                    }
                }
            }
        }

        // Row 7 Right: Bendahara
        $row7Right = $rows->item(7)->getElementsByTagNameNS($ns, 'tc')->item(1);
        if ($row7Right) {
            $this->removeRightIndentsFromNode($row7Right);

            // Remove empty spacer paragraphs so Bendahara aligns vertically on the exact same row as Kepsek
            $emptyPs = [];
            foreach ($row7Right->getElementsByTagNameNS($ns, 'p') as $p) {
                if (trim($p->textContent) === '') {
                    $emptyPs[] = $p;
                }
            }
            foreach ($emptyPs as $p) {
                $row7Right->removeChild($p);
            }

            $r7RightPs = $row7Right->getElementsByTagNameNS($ns, 'p');

            $namaBendahara = $data['nama_bendahara'] ?: '(Nama Jelas)';
            $nipBendahara = 'NIP. '.($data['nip_bendahara'] ?: '....................');
            // Auto-size font if name is long so it never wraps
            $szBendahara = mb_strlen($namaBendahara) > 28 ? '20' : '22';

            foreach ($r7RightPs as $p) {
                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }

                // Remove any centering so left edge alignment is preserved
                $jcNodes = $pPr->getElementsByTagNameNS($ns, 'jc');
                while ($jcNodes->length > 0) {
                    $pPr->removeChild($jcNodes->item(0));
                }

                // Set left indent so the block aligns under Ketua Pelaksana rather than far left
                $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
                if (! $ind) {
                    $ind = $dom->createElementNS($ns, 'w:ind');
                    $pPr->appendChild($ind);
                }
                $ind->setAttributeNS($ns, 'w:left', '500');
                if ($ind->hasAttributeNS($ns, 'right')) {
                    $ind->removeAttributeNS($ns, 'right');
                }

                if (str_contains($p->textContent, '(Nama Jelas)')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = $namaBendahara;
                        } else {
                            $t->textContent = '';
                        }
                    }

                    // Set font size on runs
                    $szNodes = $p->getElementsByTagNameNS($ns, 'sz');
                    foreach ($szNodes as $szNode) {
                        $szNode->setAttributeNS($ns, 'w:val', $szBendahara);
                    }
                } elseif (str_starts_with(trim($p->textContent), 'NIP.')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = $nipBendahara;
                        } else {
                            $t->textContent = '';
                        }
                    }

                    // Set font size on runs
                    $szNodes = $p->getElementsByTagNameNS($ns, 'sz');
                    foreach ($szNodes as $szNode) {
                        $szNode->setAttributeNS($ns, 'w:val', $szBendahara);
                    }
                }
            }
        }

        // 3. Body paragraphs after table (Ketua Komite Satuan Pendidikan)
        $body = $dom->getElementsByTagNameNS($ns, 'body')->item(0);
        if ($body) {
            $isUnderKomite = false;
            foreach ($body->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'p') {
                    $txt = trim($child->textContent);
                    if (str_contains($txt, 'Ketua Komite Satuan Pendidikan') || str_contains($txt, 'Ketua Komite')) {
                        $isUnderKomite = true;

                        continue;
                    }
                    if ($isUnderKomite && (str_contains($txt, '(Nama Jelas)') || str_contains($txt, 'Nama Jelas') || $txt === '(Nama Jelas)')) {
                        $namaKomite = $data['nama_ketua_komite'] ?: '(Nama Jelas)';
                        $tNodes = $child->getElementsByTagNameNS($ns, 't');
                        foreach ($tNodes as $tIdx => $t) {
                            if ($tIdx === 0) {
                                $t->textContent = $namaKomite;
                            } else {
                                $t->textContent = '';
                            }
                        }
                        $isUnderKomite = false;
                    }
                }
            }
        }
    }

    /**
     * Specific auto-fill logic for LaporanAwal.docx.
     *
     * @param  array<string, string>  $data
     * @param  array<string, mixed>|null  $rab
     */
    private function fillLaporanAwal(DOMDocument $dom, array $data, ?array $rab): void
    {
        $ns = self::NS_W;

        // 1. Header KOP
        $body = $dom->getElementsByTagNameNS($ns, 'body')->item(0);
        if ($body) {
            foreach ($body->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'p') {
                    if (str_contains($child->textContent, 'KOP LEMBAGA SATUAN SMP')) {
                        $tNodes = $child->getElementsByTagNameNS($ns, 't');
                        foreach ($tNodes as $idx => $t) {
                            if ($idx === 0) {
                                $t->textContent = 'KOP '.mb_strtoupper($data['nama_sekolah']);
                            } else {
                                $t->textContent = '';
                            }
                        }
                    }
                }
            }
        }

        // 2. Table 0 (Tanggal Surat)
        $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
        if ($tables->length > 0) {
            $tbl = $tables->item(0);
            $rows = $tbl->getElementsByTagNameNS($ns, 'tr');
            if ($rows->length > 0) {
                $rightTc = $rows->item(0)->getElementsByTagNameNS($ns, 'tc')->item(1);
                if ($rightTc) {
                    $ps = $rightTc->getElementsByTagNameNS($ns, 'p');
                    if ($ps->length >= 2) {
                        $p1 = $ps->item(1);
                        $tNodes = $p1->getElementsByTagNameNS($ns, 't');
                        foreach ($tNodes as $tIdx => $t) {
                            if ($tIdx === 0) {
                                $t->textContent = ': '.$data['tanggal'];
                            } else {
                                $t->textContent = '';
                            }
                        }
                    }
                }
            }
        }

        // 3. Body paragraphs
        $allPs = iterator_to_array($dom->getElementsByTagNameNS($ns, 'p'));
        foreach ($allPs as $p) {
            $txt = trim($p->textContent);

            // Identity block: Nama / Jabatan / Alamat
            if (str_contains($txt, 'Nama') && str_contains($txt, 'Jabatan') && str_contains($txt, 'Alamat Satuan SMP')) {
                $this->removeRightIndentsFromNode($p);

                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                if (! $tabs) {
                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $pPr->appendChild($tabs);
                }
                $tab = $dom->createElementNS($ns, 'w:tab');
                $tab->setAttributeNS($ns, 'w:pos', '2400');
                $tab->setAttributeNS($ns, 'w:val', 'left');
                $tabs->appendChild($tab);

                $spacing1 = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
                if (! $spacing1) {
                    $spacing1 = $dom->createElementNS($ns, 'w:spacing');
                    $pPr->appendChild($spacing1);
                }
                $spacing1->setAttributeNS($ns, 'w:after', '0');
                $spacing1->setAttributeNS($ns, 'w:line', '240');
                $spacing1->setAttributeNS($ns, 'w:lineRule', 'auto');

                // Line 1: Nama
                $r1 = $dom->createElementNS($ns, 'w:r');
                $t1 = $dom->createElementNS($ns, 'w:t');
                $t1->textContent = 'Nama';
                $r1->appendChild($t1);
                $p->appendChild($r1);

                $rTab1 = $dom->createElementNS($ns, 'w:r');
                $rTab1->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab1);

                $rVal1 = $dom->createElementNS($ns, 'w:r');
                $tVal1 = $dom->createElementNS($ns, 'w:t');
                $tVal1->setAttribute('xml:space', 'preserve');
                $tVal1->textContent = ': '.$data['nama_kepsek'];
                $rVal1->appendChild($tVal1);
                $p->appendChild($rVal1);

                // Line 2: Jabatan
                $p2 = $dom->createElementNS($ns, 'w:p');
                $pPr2 = $pPr->cloneNode(true);
                $p2->appendChild($pPr2);

                $r2 = $dom->createElementNS($ns, 'w:r');
                $t2 = $dom->createElementNS($ns, 'w:t');
                $t2->textContent = 'Jabatan';
                $r2->appendChild($t2);
                $p2->appendChild($r2);

                $rTab2 = $dom->createElementNS($ns, 'w:r');
                $rTab2->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p2->appendChild($rTab2);

                $rVal2 = $dom->createElementNS($ns, 'w:r');
                $tVal2 = $dom->createElementNS($ns, 'w:t');
                $tVal2->setAttribute('xml:space', 'preserve');
                $tVal2->textContent = ': Pengelola/Kepala Satuan SMP';
                $rVal2->appendChild($tVal2);
                $p2->appendChild($rVal2);

                // Line 3: Alamat Satuan SMP
                $p3 = $dom->createElementNS($ns, 'w:p');
                $pPr3 = $pPr->cloneNode(true);
                $spacing3 = $pPr3->getElementsByTagNameNS($ns, 'spacing')->item(0);
                if (! $spacing3) {
                    $spacing3 = $dom->createElementNS($ns, 'w:spacing');
                    $pPr3->appendChild($spacing3);
                }
                $spacing3->setAttributeNS($ns, 'w:after', '160');
                $spacing3->setAttributeNS($ns, 'w:line', '240');
                $spacing3->setAttributeNS($ns, 'w:lineRule', 'auto');
                $p3->appendChild($pPr3);

                $r3 = $dom->createElementNS($ns, 'w:r');
                $t3 = $dom->createElementNS($ns, 'w:t');
                $t3->textContent = 'Alamat Satuan SMP';
                $r3->appendChild($t3);
                $p3->appendChild($r3);

                $rTab3 = $dom->createElementNS($ns, 'w:r');
                $rTab3->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p3->appendChild($rTab3);

                $rVal3 = $dom->createElementNS($ns, 'w:r');
                $tVal3 = $dom->createElementNS($ns, 'w:t');
                $tVal3->setAttribute('xml:space', 'preserve');
                $tVal3->textContent = ': '.$data['alamat'];
                $rVal3->appendChild($tVal3);
                $p3->appendChild($rVal3);

                if ($p->nextSibling) {
                    $p->parentNode->insertBefore($p2, $p->nextSibling);
                    $p->parentNode->insertBefore($p3, $p2->nextSibling);
                } else {
                    $p->parentNode->appendChild($p2);
                    $p->parentNode->appendChild($p3);
                }

                continue;
            }

            // Statement body: Kami sampaikan bahwa SMP...
            if (str_contains($txt, 'telah menerima dana bantuan')) {
                $totalVal = 69364000;
                $nominalFmt = 'Rp '.number_format($totalVal, 2, ',', '.');
                $terbilangFmt = $this->terbilang($totalVal).' Rupiah';

                $bankName = ! empty($data['nama_bank']) ? preg_replace('/^Bank\s+/i', '', trim($data['nama_bank'])) : '';
                $bankFmt = $bankName !== '' ? 'Bank '.$bankName : 'Bank ………………………………';
                $anFmt = ! empty($data['atas_nama_rekening']) ? $data['atas_nama_rekening'] : '………………………………………………………………';

                $sekolahClean = preg_replace('/^SMP\s+/i', '', $data['nama_sekolah']);
                $statementText = 'Kami sampaikan bahwa SMP '.$sekolahClean.' telah menerima dana bantuan Peralatan TIK Tahun 2026, sebesar '.$nominalFmt.' ('.$terbilangFmt.') melalui rekening '.$bankFmt.' a.n '.$anFmt.'.';

                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = $statementText;
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // Titimangsa: ………..,……………………….2026
            if (preg_match('/^[.…,\s]+2026/', $txt)) {
                $this->applySignatureParagraphStyle($p, 0);
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = $data['kabupaten'].', '.$data['tanggal'];
                    } else {
                        $t->textContent = '';
                    }
                }

                $szNodes = $p->getElementsByTagNameNS($ns, 'sz');
                foreach ($szNodes as $sz) {
                    $sz->setAttributeNS($ns, 'w:val', '24');
                }

                continue;
            }

            // Kepala Satuan SMP ……………………….
            if (str_starts_with($txt, 'Kepala Satuan SMP')) {
                $this->applySignatureParagraphStyle($p, 0);
                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                // Line 1: Kepala Satuan SMP
                $rRole = $dom->createElementNS($ns, 'w:r');
                $tRole = $dom->createElementNS($ns, 'w:t');
                $tRole->textContent = 'Kepala Satuan SMP';
                $rRole->appendChild($tRole);
                $p->appendChild($rRole);

                // Line 2: Nama Sekolah
                $pSchool = $dom->createElementNS($ns, 'w:p');
                $this->applySignatureParagraphStyle($pSchool, 450);
                $rSchool = $dom->createElementNS($ns, 'w:r');
                $tSchool = $dom->createElementNS($ns, 'w:t');
                $tSchool->textContent = $data['nama_sekolah'];
                $rSchool->appendChild($tSchool);
                $pSchool->appendChild($rSchool);

                if ($p->nextSibling) {
                    $p->parentNode->insertBefore($pSchool, $p->nextSibling);
                } else {
                    $p->parentNode->appendChild($pSchool);
                }

                continue;
            }

            // stempel sekolah dan ttd
            if (str_contains($txt, 'stempel sekolah dan ttd')) {
                $this->applySignatureParagraphStyle($p, 450);

                continue;
            }

            // Nama Kepsek (in signature)
            if ($txt === 'Nama' || $txt === 'Nama ') {
                $this->applySignatureParagraphStyle($p, 0);
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = $data['nama_kepsek'];
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // NIP Kepsek (in signature)
            if (str_starts_with($txt, 'NIP.')) {
                $this->applySignatureParagraphStyle($p, 0);
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'NIP. '.($data['nip_kepsek'] ?: '....................');
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // Tembusan
            if (str_contains($txt, 'Kepala Dinas Pendidikan Kab/Kota')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Kepala Dinas Pendidikan '.$data['kabupaten'];
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }
        }
    }

    /**
     * Specific auto-fill logic for LaporanAkhir.docx (Sampul / Cover).
     *
     * @param  array<string, string>  $data
     */
    private function fillLaporanAkhir(DOMDocument $dom, array $data): void
    {
        $ns = self::NS_W;

        $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
        if ($tables->length === 0) {
            return;
        }

        $tbl = $tables->item(0);
        $rows = $tbl->getElementsByTagNameNS($ns, 'tr');
        if ($rows->length < 11) {
            return;
        }

        $rtRwNoParts = [];
        if (! empty($data['rt'])) {
            $rtRwNoParts[] = 'RT '.$data['rt'];
        }
        if (! empty($data['rw'])) {
            $rtRwNoParts[] = 'RW '.$data['rw'];
        }
        if (! empty($data['nomor_bangunan'])) {
            $rtRwNoParts[] = 'No. '.$data['nomor_bangunan'];
        }
        $rtRwNo = implode(' / ', $rtRwNoParts);

        $fieldMapping = [
            0 => ': '.($data['nama_sekolah'] ?: '....................................................'),
            1 => ': '.($data['npsn'] ?: '....................................................'),
            2 => 'Jalan : '.($data['alamat'] ?: str_repeat('.', 44)),
            3 => 'RT/RW/No. : '.($rtRwNo ?: str_repeat('.', 38)),
            4 => 'Desa/Kel : '.($data['desa_kelurahan'] ?: str_repeat('.', 40)),
            5 => 'Kecamatan : '.($data['kecamatan'] ?: str_repeat('.', 38)),
            6 => 'Kab/Kota : '.($data['kabupaten'] ?: str_repeat('.', 42)),
            7 => 'Provinsi : '.($data['provinsi'] ?: str_repeat('.', 42)),
            8 => 'Kode Pos : '.($data['kode_pos'] ?: str_repeat('.', 40)),
            9 => 'Nama Pengelola/Penanggung Jawab : '.($data['nama_kepsek'] ?: str_repeat('.', 15)),
            10 => 'No Telp/HP/Fax : '.($data['no_telepon'] ?: str_repeat('.', 32)),
        ];

        foreach ($fieldMapping as $rowIndex => $valText) {
            $row = $rows->item($rowIndex);
            if (! $row) {
                continue;
            }

            $cells = $row->getElementsByTagNameNS($ns, 'tc');
            if ($cells->length < 2) {
                continue;
            }

            $tc1 = $cells->item(1);
            $p = $tc1->getElementsByTagNameNS($ns, 'p')->item(0);
            if (! $p) {
                $p = $dom->createElementNS($ns, 'w:p');
                $tc1->appendChild($p);
            }

            // Remove existing direct runs
            $runs = [];
            foreach ($p->childNodes as $c) {
                if ($c->localName === 'r') {
                    $runs[] = $c;
                }
            }
            foreach ($runs as $r) {
                $p->removeChild($r);
            }

            // Ensure compact paragraph spacing
            $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
            if (! $pPr) {
                $pPr = $dom->createElementNS($ns, 'w:pPr');
                $p->insertBefore($pPr, $p->firstChild);
            }
            $spacing = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
            if (! $spacing) {
                $spacing = $dom->createElementNS($ns, 'w:spacing');
                $pPr->appendChild($spacing);
            }
            $spacing->setAttributeNS($ns, 'w:after', '0');

            $r = $dom->createElementNS($ns, 'w:r');
            $rPr = $dom->createElementNS($ns, 'w:rPr');

            $sz = $dom->createElementNS($ns, 'w:sz');
            $sz->setAttributeNS($ns, 'w:val', '24');
            $rPr->appendChild($sz);

            $r->appendChild($rPr);

            $t = $dom->createElementNS($ns, 'w:t');
            $t->setAttribute('xml:space', 'preserve');
            $t->textContent = $valText;
            $r->appendChild($t);

            $p->appendChild($r);
        }
    }

    /**
     * Specific auto-fill logic for BAST.docx (Berita Acara Serah Terima).
     *
     * @param  array<string, string>  $data
     * @param  array<string, mixed>|null  $rab
     */
    private function fillBast(DOMDocument $dom, array $data, ?array $rab): void
    {
        $ns = self::NS_W;

        $totalReceived = 69364000;
        $totalUsed = (float) ($rab['total_harga'] ?? 69000000);
        $sisaDana = max(0, $totalReceived - $totalUsed);

        $now = Carbon::now('Asia/Jakarta');
        $hariIndonesia = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $hari = $hariIndonesia[$now->format('l')] ?? 'Rabu';
        $tgl = $now->day;
        $bln = $bulanIndonesia[$now->month] ?? 'September';
        $thn = $now->year;

        $allPs = iterator_to_array($dom->getElementsByTagNameNS($ns, 'p'));
        $seenMaterai = false;

        foreach ($allPs as $pIdx => $p) {
            $txt = trim($p->textContent);

            // 1. KOP (P0)
            if ($pIdx === 0 || str_contains($txt, 'KOP SURAT')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'KOP '.mb_strtoupper($data['nama_sekolah']);
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 2. Hari / Tanggal (P3)
            if (str_contains($txt, 'Pada hari ini') && str_contains($txt, 'yang bertanda tangan di bawah ini')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                $newText = "Pada hari ini $hari tanggal $tgl bulan $bln tahun $thn , yang bertanda tangan di bawah ini:";
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = $newText;
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 3. PIHAK PERTAMA (P4: Nama / Jabatan / Alamat)
            if (str_contains($txt, 'Nama') && str_contains($txt, 'Jabatan : Kepala') && str_contains($txt, 'Alamat')) {
                $this->removeRightIndentsFromNode($p);

                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $jc = $pPr->getElementsByTagNameNS($ns, 'jc')->item(0);
                if (! $jc) {
                    $jc = $dom->createElementNS($ns, 'w:jc');
                    $pPr->appendChild($jc);
                }
                $jc->setAttributeNS($ns, 'w:val', 'left');

                $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
                if (! $ind) {
                    $ind = $dom->createElementNS($ns, 'w:ind');
                    $pPr->appendChild($ind);
                }
                $ind->setAttributeNS($ns, 'w:left', '488');
                $ind->setAttributeNS($ns, 'w:hanging', '488');

                $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                if (! $tabs) {
                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $pPr->appendChild($tabs);
                }
                $tab1 = $dom->createElementNS($ns, 'w:tab');
                $tab1->setAttributeNS($ns, 'w:val', 'left');
                $tab1->setAttributeNS($ns, 'w:pos', '1400');
                $tabs->appendChild($tab1);

                // Line 1: Nama
                $r1_lbl = $dom->createElementNS($ns, 'w:r');
                $t1_lbl = $dom->createElementNS($ns, 'w:t');
                $t1_lbl->textContent = 'Nama';
                $r1_lbl->appendChild($t1_lbl);
                $p->appendChild($r1_lbl);

                $rTab1 = $dom->createElementNS($ns, 'w:r');
                $rTab1->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab1);

                $r1_val = $dom->createElementNS($ns, 'w:r');
                $t1_val = $dom->createElementNS($ns, 'w:t');
                $t1_val->setAttribute('xml:space', 'preserve');
                $t1_val->textContent = ': '.($data['nama_kepsek'] ?: '....................................................');
                $r1_val->appendChild($t1_val);
                $p->appendChild($r1_val);

                $rBr1 = $dom->createElementNS($ns, 'w:r');
                $rBr1->appendChild($dom->createElementNS($ns, 'w:br'));
                $p->appendChild($rBr1);

                // Line 2: Jabatan
                $r2_lbl = $dom->createElementNS($ns, 'w:r');
                $t2_lbl = $dom->createElementNS($ns, 'w:t');
                $t2_lbl->textContent = 'Jabatan';
                $r2_lbl->appendChild($t2_lbl);
                $p->appendChild($r2_lbl);

                $rTab2 = $dom->createElementNS($ns, 'w:r');
                $rTab2->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab2);

                $r2_val = $dom->createElementNS($ns, 'w:r');
                $t2_val = $dom->createElementNS($ns, 'w:t');
                $t2_val->setAttribute('xml:space', 'preserve');
                $t2_val->textContent = ': Kepala '.($data['nama_sekolah'] ?: '....................................................');
                $r2_val->appendChild($t2_val);
                $p->appendChild($r2_val);

                $rBr2 = $dom->createElementNS($ns, 'w:r');
                $rBr2->appendChild($dom->createElementNS($ns, 'w:br'));
                $p->appendChild($rBr2);

                // Line 3: Alamat
                $r3_lbl = $dom->createElementNS($ns, 'w:r');
                $t3_lbl = $dom->createElementNS($ns, 'w:t');
                $t3_lbl->textContent = 'Alamat';
                $r3_lbl->appendChild($t3_lbl);
                $p->appendChild($r3_lbl);

                $rTab3 = $dom->createElementNS($ns, 'w:r');
                $rTab3->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab3);

                $r3_val = $dom->createElementNS($ns, 'w:r');
                $t3_val = $dom->createElementNS($ns, 'w:t');
                $t3_val->setAttribute('xml:space', 'preserve');
                $t3_val->textContent = ': '.($data['alamat'] ?: '....................................................');
                $r3_val->appendChild($t3_val);
                $p->appendChild($r3_val);

                continue;
            }

            // 3.5 PIHAK KEDUA Nama (P6) & Jabatan (P7)
            if (str_starts_with($txt, 'Nama') && str_contains($txt, '…') && ! str_contains($txt, 'Jabatan')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Nama          : '.($data['nama_ppk'] ?? 'Hendro Sucipto, S.Kom.');
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            if (str_starts_with($txt, 'Jabatan') && str_contains($txt, 'Pejabat Pembuat Komitmen')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Jabatan       : Pejabat Pembuat Komitmen Direktorat Sekolah Menengah Pertama';
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 4. PIHAK KEDUA Alamat (P8)
            if (str_contains($txt, 'yang selanjutnya disebut sebagai PIHAK KEDUA')) {
                $this->removeRightIndentsFromNode($p);
                $alamatPpk = $data['alamat_ppk'] ?? 'Kompleks Kemendikbudristek Gedung E Lt. 17, Jl. Jenderal Sudirman, Senayan, Jakarta Pusat';
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Alamat        : '.$alamatPpk.', yang selanjutnya disebut sebagai PIHAK KEDUA';
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 5. Dana Diterima (P13)
            if (str_contains($txt, 'Jumlah total dana yang telah diterima')) {
                $fmt = 'Rp. '.number_format($totalReceived, 2, ',', '.').' (Enam Puluh Sembilan Juta Tiga Ratus Enam Puluh Empat Ribu Rupiah)';
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Jumlah total dana yang telah diterima  : '.$fmt;
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 6. Dana Dipergunakan (P14)
            if (str_contains($txt, 'Jumlah total dana yang dipergunakan')) {
                $fmt = 'Rp. '.number_format($totalUsed, 2, ',', '.').' ('.$this->terbilang($totalUsed).' Rupiah)';
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Jumlah total dana yang dipergunakan  : '.$fmt;
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 7. Sisa Dana (P15)
            if (str_contains($txt, 'Jumlah total sisa dana')) {
                $fmt = 'Rp. '.number_format($sisaDana, 2, ',', '.').' ('.$this->terbilang($sisaDana).' Rupiah)';
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Jumlah total sisa dana                              : '.$fmt;
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 8. Bukti Pengeluaran (P16 - Item 3) - preserve BOLD on PIHAK PERTAMA, only replace in regular text run
            if (str_contains($txt, 'bukti-bukti pengeluaran dana Bantuan Pemerintah')) {
                $brNodes = $p->getElementsByTagNameNS($ns, 'br');
                while ($brNodes->length > 0) {
                    $br = $brNodes->item(0);
                    $br->parentNode->removeChild($br);
                }

                $fmt = 'Rp. '.number_format($totalUsed, 2, ',', '.').' ('.$this->terbilang($totalUsed).' Rupiah)';
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $t) {
                    if (str_contains($t->textContent, 'Pengadaan Sarana TIK sebesar')) {
                        $t->textContent = "Pengadaan Sarana TIK sebesar $fmt telah disimpan sesuai dengan ketentuan untuk kelengkapan administrasi dan keperluan pemeriksaan aparat pengawas fungsional.";
                    }
                }

                continue;
            }

            // 9. Penyerahan Barang (P17 - Item 4) - preserve BOLD on PIHAK PERTAMA / PIHAK KEDUA
            if (str_contains($txt, 'PIHAK PERTAMA menyerahkan kepada PIHAK KEDUA') && str_contains($txt, 'berupa pembelian Pengadaan Sarana TIK')) {
                $brNodes = $p->getElementsByTagNameNS($ns, 'br');
                while ($brNodes->length > 0) {
                    $br = $brNodes->item(0);
                    $br->parentNode->removeChild($br);
                }

                $fmt = 'Rp. '.number_format($totalUsed, 2, ',', '.').' ('.$this->terbilang($totalUsed).' Rupiah)';
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $t) {
                    if (str_contains($t->textContent, 'Rp.') && str_contains($t->textContent, '...')) {
                        $t->textContent = $fmt.'.';
                    }
                }

                continue;
            }

            // 10. Setor Sisa Dana (P18 - Item 5) - preserve BOLD on PIHAK PERTAMA
            if (str_contains($txt, 'telah menyetorkan sisa dana bantuan ke Kas Negara')) {
                $brNodes = $p->getElementsByTagNameNS($ns, 'br');
                while ($brNodes->length > 0) {
                    $br = $brNodes->item(0);
                    $br->parentNode->removeChild($br);
                }

                $fmt = 'Rp. '.number_format($sisaDana, 2, ',', '.').' ('.$this->terbilang($sisaDana).' Rupiah)';
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $t) {
                    if (str_contains($t->textContent, 'Rp.') && str_contains($t->textContent, '...')) {
                        $t->textContent = " $fmt sebagaimana Bukti Penerimaan Negara (BPN) terlampir. *) Demikian berita acara ini dibuat untuk dipergunakan sebagaimana mestinya.";
                    }
                }

                continue;
            }

            // 11. Signature Header (P19)
            if (str_contains($txt, 'PIHAK PERTAMA') && str_contains($txt, 'PIHAK KEDUA') && str_contains($txt, 'Kepala')) {
                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $spacing = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
                if (! $spacing) {
                    $spacing = $dom->createElementNS($ns, 'w:spacing');
                    $pPr->appendChild($spacing);
                }
                $spacing->setAttributeNS($ns, 'w:before', '400');
                $spacing->setAttributeNS($ns, 'w:after', '150');

                $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
                if (! $ind) {
                    $ind = $dom->createElementNS($ns, 'w:ind');
                    $pPr->appendChild($ind);
                }
                $ind->setAttributeNS($ns, 'w:left', '0');

                $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                if ($tabs) {
                    while ($tabs->firstChild) {
                        $tabs->removeChild($tabs->firstChild);
                    }
                } else {
                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $pPr->appendChild($tabs);
                }
                $tab = $dom->createElementNS($ns, 'w:tab');
                $tab->setAttributeNS($ns, 'w:val', 'left');
                $tab->setAttributeNS($ns, 'w:pos', '5700');
                $tabs->appendChild($tab);

                // Line 1: PIHAK PERTAMA [TAB] PIHAK KEDUA
                $r1_1 = $dom->createElementNS($ns, 'w:r');
                $t1_1 = $dom->createElementNS($ns, 'w:t');
                $t1_1->textContent = 'PIHAK PERTAMA';
                $r1_1->appendChild($t1_1);
                $p->appendChild($r1_1);

                $rTab1 = $dom->createElementNS($ns, 'w:r');
                $rTab1->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab1);

                $r1_2 = $dom->createElementNS($ns, 'w:r');
                $t1_2 = $dom->createElementNS($ns, 'w:t');
                $t1_2->textContent = 'PIHAK KEDUA';
                $r1_2->appendChild($t1_2);
                $p->appendChild($r1_2);

                $rBr = $dom->createElementNS($ns, 'w:r');
                $rBr->appendChild($dom->createElementNS($ns, 'w:br'));
                $p->appendChild($rBr);

                // Line 2: Kepala [Sekolah] [TAB] PPK Direktorat Sekolah Menengah Pertama
                $r2_1 = $dom->createElementNS($ns, 'w:r');
                $t2_1 = $dom->createElementNS($ns, 'w:t');
                $t2_1->textContent = 'Kepala '.$data['nama_sekolah'];
                $r2_1->appendChild($t2_1);
                $p->appendChild($r2_1);

                $rTab2 = $dom->createElementNS($ns, 'w:r');
                $rTab2->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab2);

                $r2_2 = $dom->createElementNS($ns, 'w:r');
                $t2_2 = $dom->createElementNS($ns, 'w:t');
                $t2_2->textContent = 'PPK Direktorat Sekolah Menengah Pertama';
                $r2_2->appendChild($t2_2);
                $p->appendChild($r2_2);

                continue;
            }

            // 12. Materai (P20)
            if ($txt === 'materai') {
                $seenMaterai = true;
                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $jc = $pPr->getElementsByTagNameNS($ns, 'jc')->item(0);
                if (! $jc) {
                    $jc = $dom->createElementNS($ns, 'w:jc');
                    $pPr->appendChild($jc);
                }
                $jc->setAttributeNS($ns, 'w:val', 'left');

                $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
                if (! $ind) {
                    $ind = $dom->createElementNS($ns, 'w:ind');
                    $pPr->appendChild($ind);
                }
                $ind->setAttributeNS($ns, 'w:left', '3800');

                $spacing = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
                if (! $spacing) {
                    $spacing = $dom->createElementNS($ns, 'w:spacing');
                    $pPr->appendChild($spacing);
                }
                $spacing->setAttributeNS($ns, 'w:before', '300');
                $spacing->setAttributeNS($ns, 'w:after', '250');

                continue;
            }

            // 13. Signature Names (P21)
            if ($seenMaterai && str_contains($txt, '……………………….')) {
                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
                if (! $ind) {
                    $ind = $dom->createElementNS($ns, 'w:ind');
                    $pPr->appendChild($ind);
                }
                $ind->setAttributeNS($ns, 'w:left', '0');

                $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                if ($tabs) {
                    while ($tabs->firstChild) {
                        $tabs->removeChild($tabs->firstChild);
                    }
                } else {
                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $pPr->appendChild($tabs);
                }
                $tab = $dom->createElementNS($ns, 'w:tab');
                $tab->setAttributeNS($ns, 'w:val', 'left');
                $tab->setAttributeNS($ns, 'w:pos', '5700');
                $tabs->appendChild($tab);

                $r1 = $dom->createElementNS($ns, 'w:r');
                $rPr1 = $dom->createElementNS($ns, 'w:rPr');
                $sz1 = $dom->createElementNS($ns, 'w:sz');
                $sz1->setAttributeNS($ns, 'w:val', '24');
                $rPr1->appendChild($sz1);
                $r1->appendChild($rPr1);
                $t1 = $dom->createElementNS($ns, 'w:t');
                $t1->textContent = $data['nama_kepsek'] ?: '....................................................';
                $r1->appendChild($t1);
                $p->appendChild($r1);

                $rTab = $dom->createElementNS($ns, 'w:r');
                $rTab->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab);

                $r2 = $dom->createElementNS($ns, 'w:r');
                $rPr2 = $dom->createElementNS($ns, 'w:rPr');
                $sz2 = $dom->createElementNS($ns, 'w:sz');
                $sz2->setAttributeNS($ns, 'w:val', '24');
                $rPr2->appendChild($sz2);
                $r2->appendChild($rPr2);
                $t2 = $dom->createElementNS($ns, 'w:t');
                $t2->textContent = $data['nama_ppk'] ?? 'Hendro Sucipto, S.Kom.';
                $r2->appendChild($t2);
                $p->appendChild($r2);

                continue;
            }

            // 14. Signature NIP (P22)
            if ($seenMaterai && (str_starts_with($txt, 'NIP. …') || (str_starts_with($txt, 'NIP.') && str_contains($txt, '…')))) {
                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
                if (! $ind) {
                    $ind = $dom->createElementNS($ns, 'w:ind');
                    $pPr->appendChild($ind);
                }
                $ind->setAttributeNS($ns, 'w:left', '0');

                $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                if ($tabs) {
                    while ($tabs->firstChild) {
                        $tabs->removeChild($tabs->firstChild);
                    }
                } else {
                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $pPr->appendChild($tabs);
                }
                $tab = $dom->createElementNS($ns, 'w:tab');
                $tab->setAttributeNS($ns, 'w:val', 'left');
                $tab->setAttributeNS($ns, 'w:pos', '5700');
                $tabs->appendChild($tab);

                $r1 = $dom->createElementNS($ns, 'w:r');
                $rPr1 = $dom->createElementNS($ns, 'w:rPr');
                $sz1 = $dom->createElementNS($ns, 'w:sz');
                $sz1->setAttributeNS($ns, 'w:val', '24');
                $rPr1->appendChild($sz1);
                $r1->appendChild($rPr1);
                $t1 = $dom->createElementNS($ns, 'w:t');
                $t1->textContent = 'NIP. '.($data['nip_kepsek'] ?: '....................');
                $r1->appendChild($t1);
                $p->appendChild($r1);

                $rTab = $dom->createElementNS($ns, 'w:r');
                $rTab->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab);

                $r2 = $dom->createElementNS($ns, 'w:r');
                $rPr2 = $dom->createElementNS($ns, 'w:rPr');
                $sz2 = $dom->createElementNS($ns, 'w:sz');
                $sz2->setAttributeNS($ns, 'w:val', '24');
                $rPr2->appendChild($sz2);
                $r2->appendChild($rPr2);
                $t2 = $dom->createElementNS($ns, 'w:t');
                $t2->textContent = 'NIP. '.($data['nip_ppk'] ?: '………………………………………………');
                $r2->appendChild($t2);
                $p->appendChild($r2);

                continue;
            }
        }
    }

    /**
     * Indonesian number spelling (terbilang).
     */
    private function terbilang(float|int $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';

        if ($nilai < 12) {
            $temp = ' '.$huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $temp = $this->terbilang($nilai - 10).' Belas';
        } elseif ($nilai < 100) {
            $temp = $this->terbilang((int) ($nilai / 10)).' Puluh '.$this->terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' Seratus '.$this->terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = $this->terbilang((int) ($nilai / 100)).' Ratus '.$this->terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' Seribu '.$this->terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = $this->terbilang((int) ($nilai / 1000)).' Ribu '.$this->terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = $this->terbilang((int) ($nilai / 1000000)).' Juta '.$this->terbilang($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = $this->terbilang((int) ($nilai / 1000000000)).' Miliar '.$this->terbilang(fmod($nilai, 1000000000));
        }

        return preg_replace('/\s+/', ' ', trim($temp));
    }

    /**
     * Apply clean right-column signature paragraph style (left indent 5000 dxa, no right indent, no jc).
     */
    private function applySignatureParagraphStyle(DOMElement $p, int $spacingAfter = 0): void
    {
        $ns = self::NS_W;
        $dom = $p->ownerDocument;

        $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
        if (! $pPr) {
            $pPr = $dom->createElementNS($ns, 'w:pPr');
            $p->insertBefore($pPr, $p->firstChild);
        }

        // Remove jc
        $jcNodes = $pPr->getElementsByTagNameNS($ns, 'jc');
        while ($jcNodes->length > 0) {
            $pPr->removeChild($jcNodes->item(0));
        }

        // Set left indent
        $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
        if (! $ind) {
            $ind = $dom->createElementNS($ns, 'w:ind');
            $pPr->appendChild($ind);
        }
        $ind->setAttributeNS($ns, 'w:left', '5200');
        if ($ind->hasAttributeNS($ns, 'right')) {
            $ind->removeAttributeNS($ns, 'right');
        }
        if ($ind->hasAttribute('w:right')) {
            $ind->removeAttribute('w:right');
        }

        // Set spacing
        $spacing = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
        if (! $spacing) {
            $spacing = $dom->createElementNS($ns, 'w:spacing');
            $pPr->appendChild($spacing);
        }
        $spacing->setAttributeNS($ns, 'w:after', (string) $spacingAfter);
        $spacing->setAttributeNS($ns, 'w:line', '240');
        $spacing->setAttributeNS($ns, 'w:lineRule', 'auto');
    }

    /**
     * Helper to set multiline text in a table cell.
     */
    private function setTableCellText(DOMDocument $dom, DOMElement $tc, string $text, string $align = 'left', bool $bold = false, int $fontSize = 20): void
    {
        $ns = self::NS_W;

        $pNodes = [];
        foreach ($tc->childNodes as $c) {
            if ($c->localName === 'p') {
                $pNodes[] = $c;
            }
        }
        foreach ($pNodes as $p) {
            $tc->removeChild($p);
        }

        $p = $dom->createElementNS($ns, 'w:p');
        $pPr = $dom->createElementNS($ns, 'w:pPr');

        $spacing = $dom->createElementNS($ns, 'w:spacing');
        $spacing->setAttributeNS($ns, 'w:after', '40');
        $spacing->setAttributeNS($ns, 'w:line', '240');
        $spacing->setAttributeNS($ns, 'w:lineRule', 'auto');
        $pPr->appendChild($spacing);

        if ($align !== 'left') {
            $jc = $dom->createElementNS($ns, 'w:jc');
            $jc->setAttributeNS($ns, 'w:val', $align);
            $pPr->appendChild($jc);
        }
        $p->appendChild($pPr);

        $lines = explode("\n", $text);
        foreach ($lines as $lIdx => $line) {
            if ($lIdx > 0) {
                $rBr = $dom->createElementNS($ns, 'w:r');
                $br = $dom->createElementNS($ns, 'w:br');
                $rBr->appendChild($br);
                $p->appendChild($rBr);
            }
            $r = $dom->createElementNS($ns, 'w:r');
            $rPr = $dom->createElementNS($ns, 'w:rPr');
            $sz = $dom->createElementNS($ns, 'w:sz');
            $sz->setAttributeNS($ns, 'w:val', (string) $fontSize);
            $rPr->appendChild($sz);
            if ($bold) {
                $b = $dom->createElementNS($ns, 'w:b');
                $rPr->appendChild($b);
            }
            $r->appendChild($rPr);

            $t = $dom->createElementNS($ns, 'w:t');
            $t->setAttribute('xml:space', 'preserve');
            $t->textContent = $line;
            $r->appendChild($t);
            $p->appendChild($r);
        }

        $tc->appendChild($p);
    }

    /**
     * Remove all right indents from all descendant paragraphs in a node.
     */
    private function removeRightIndentsFromNode(DOMElement $node): void
    {
        $ns = self::NS_W;
        $inds = $node->getElementsByTagNameNS($ns, 'ind');
        foreach ($inds as $ind) {
            if ($ind->hasAttributeNS($ns, 'right') || $ind->hasAttribute('w:right')) {
                $ind->removeAttributeNS($ns, 'right');
                $ind->removeAttribute('w:right');
            }
        }
    }

    /**
     * Split "Yang bertanda tangan di bawah ini, saya :" and "Nama : [nama]" into two distinct paragraphs.
     * The Nama paragraph uses identical tabs and indentations as NIP/NIK, Jabatan, and Alamat for perfect alignment.
     */
    private function splitYangBertandaTanganDanNama(DOMElement $p, string $namaKepsek): void
    {
        $dom = $p->ownerDocument;

        $this->removeRightIndentation($p);

        $runs = $this->getDirectRuns($p);
        foreach ($runs as $r) {
            $p->removeChild($r);
        }

        $rIntro = $dom->createElementNS(self::NS_W, 'w:r');
        $tIntro = $dom->createElementNS(self::NS_W, 'w:t');
        $tIntro->setAttribute('xml:space', 'preserve');
        $tIntro->textContent = 'Yang bertanda tangan di bawah ini, saya :';
        $rIntro->appendChild($tIntro);
        $p->appendChild($rIntro);

        $pNama = $dom->createElementNS(self::NS_W, 'w:p');

        $pPr = $dom->createElementNS(self::NS_W, 'w:pPr');

        $pStyle = $dom->createElementNS(self::NS_W, 'w:pStyle');
        $pStyle->setAttributeNS(self::NS_W, 'w:val', 'BodyText');
        $pPr->appendChild($pStyle);

        $tabs = $dom->createElementNS(self::NS_W, 'w:tabs');
        $tab = $dom->createElementNS(self::NS_W, 'w:tab');
        $tab->setAttributeNS(self::NS_W, 'w:pos', '1683');
        $tab->setAttributeNS(self::NS_W, 'w:val', 'left');
        $tab->setAttributeNS(self::NS_W, 'w:leader', 'none');
        $tabs->appendChild($tab);
        $pPr->appendChild($tabs);

        $spacing = $dom->createElementNS(self::NS_W, 'w:spacing');
        $spacing->setAttributeNS(self::NS_W, 'w:before', '35');
        $pPr->appendChild($spacing);

        $ind = $dom->createElementNS(self::NS_W, 'w:ind');
        $ind->setAttributeNS(self::NS_W, 'w:left', '122');
        $pPr->appendChild($ind);

        $pNama->appendChild($pPr);

        $rNamaLabel = $dom->createElementNS(self::NS_W, 'w:r');
        $tNamaLabel = $dom->createElementNS(self::NS_W, 'w:t');
        $tNamaLabel->textContent = 'Nama';
        $rNamaLabel->appendChild($tNamaLabel);
        $pNama->appendChild($rNamaLabel);

        $rTab = $dom->createElementNS(self::NS_W, 'w:r');
        $tabElem = $dom->createElementNS(self::NS_W, 'w:tab');
        $rTab->appendChild($tabElem);
        $pNama->appendChild($rTab);

        $rColon = $dom->createElementNS(self::NS_W, 'w:r');
        $tColon = $dom->createElementNS(self::NS_W, 'w:t');
        $tColon->setAttribute('xml:space', 'preserve');
        $tColon->textContent = ': '.$namaKepsek;
        $rColon->appendChild($tColon);
        $pNama->appendChild($rColon);

        if ($p->nextSibling) {
            $p->parentNode->insertBefore($pNama, $p->nextSibling);
        } else {
            $p->parentNode->appendChild($pNama);
        }
    }

    /**
     * Remove w:right constraint on w:ind to give full width.
     */
    private function removeRightIndentation(DOMElement $p): void
    {
        $indNodes = $p->getElementsByTagNameNS(self::NS_W, 'ind');
        foreach ($indNodes as $ind) {
            if ($ind->hasAttributeNS(self::NS_W, 'right') || $ind->hasAttribute('w:right')) {
                $ind->removeAttributeNS(self::NS_W, 'right');
                $ind->removeAttribute('w:right');
            }
        }
    }

    /**
     * Append a value with a space after ':' in the paragraph.
     */
    private function appendDirectRunValue(DOMElement $p, string $value): void
    {
        $runs = $this->getDirectRuns($p);
        $lastRun = end($runs);
        if (! $lastRun) {
            return;
        }

        $rPr = $this->getDirectRPr($lastRun);

        $newRun = $p->ownerDocument->createElementNS(self::NS_W, 'w:r');
        if ($rPr) {
            $newRun->appendChild($rPr->cloneNode(true));
        }
        $newText = $p->ownerDocument->createElementNS(self::NS_W, 'w:t');
        $newText->setAttribute('xml:space', 'preserve');
        $newText->textContent = ' '.$value;
        $newRun->appendChild($newText);
        $p->appendChild($newRun);
    }

    /**
     * Replace paragraph content with line 1, and insert a new paragraph immediately after with line 2.
     */
    private function splitIntoTwoParagraphs(DOMElement $p, string $line1, string $line2): void
    {
        $runs = $this->getDirectRuns($p);
        if (empty($runs)) {
            return;
        }

        $rPr = $this->getDirectRPr($runs[0]);
        $pPr = $this->getDirectPPr($p);

        foreach ($runs as $r) {
            $hasMc = $r->getElementsByTagName('AlternateContent')->length > 0;
            if (! $hasMc) {
                $p->removeChild($r);
            }
        }

        $r1 = $p->ownerDocument->createElementNS(self::NS_W, 'w:r');
        if ($rPr) {
            $r1->appendChild($rPr->cloneNode(true));
        }
        $t1 = $p->ownerDocument->createElementNS(self::NS_W, 'w:t');
        $t1->setAttribute('xml:space', 'preserve');
        $t1->textContent = $line1;
        $r1->appendChild($t1);
        $p->appendChild($r1);

        $p2 = $p->ownerDocument->createElementNS(self::NS_W, 'w:p');
        if ($pPr) {
            $p2->appendChild($pPr->cloneNode(true));
        }
        $r2 = $p->ownerDocument->createElementNS(self::NS_W, 'w:r');
        if ($rPr) {
            $r2->appendChild($rPr->cloneNode(true));
        }
        $t2 = $p->ownerDocument->createElementNS(self::NS_W, 'w:t');
        $t2->setAttribute('xml:space', 'preserve');
        $t2->textContent = $line2;
        $r2->appendChild($t2);
        $p2->appendChild($r2);

        if ($p->nextSibling) {
            $p->parentNode->insertBefore($p2, $p->nextSibling);
        } else {
            $p->parentNode->appendChild($p2);
        }
    }

    /**
     * Replace signature block paragraph with Name and NIP paragraph.
     */
    private function splitSignatureIntoTwoParagraphs(DOMElement $p, string $nama, string $nip): void
    {
        $runs = $this->getDirectRuns($p);
        if (empty($runs)) {
            return;
        }

        $rPr = $this->getDirectRPr($runs[0]);
        $pPr = $this->getDirectPPr($p);

        foreach ($runs as $r) {
            $hasMc = $r->getElementsByTagName('AlternateContent')->length > 0;
            if (! $hasMc) {
                $p->removeChild($r);
            }
        }

        $rName = $p->ownerDocument->createElementNS(self::NS_W, 'w:r');
        if ($rPr) {
            $rName->appendChild($rPr->cloneNode(true));
        }
        $tName = $p->ownerDocument->createElementNS(self::NS_W, 'w:t');
        $tName->setAttribute('xml:space', 'preserve');
        $tName->textContent = $nama;
        $rName->appendChild($tName);
        $p->appendChild($rName);

        if ($nip !== '') {
            $pNip = $dom = $p->ownerDocument->createElementNS(self::NS_W, 'w:p');
            if ($pPr) {
                $pNip->appendChild($pPr->cloneNode(true));
            }
            $rNip = $p->ownerDocument->createElementNS(self::NS_W, 'w:r');
            if ($rPr) {
                $rNip->appendChild($rPr->cloneNode(true));
            }
            $tNip = $p->ownerDocument->createElementNS(self::NS_W, 'w:t');
            $tNip->setAttribute('xml:space', 'preserve');
            $tNip->textContent = $nip;
            $rNip->appendChild($tNip);
            $pNip->appendChild($rNip);

            if ($p->nextSibling) {
                $p->parentNode->insertBefore($pNip, $p->nextSibling);
            } else {
                $p->parentNode->appendChild($pNip);
            }
        }
    }

    /**
     * Replace text in direct runs of a paragraph.
     */
    private function replaceDirectRunsText(DOMElement $p, string $search, string $replace): void
    {
        $runs = $this->getDirectRuns($p);
        $textNodes = [];
        $full = '';

        foreach ($runs as $r) {
            $hasMc = $r->getElementsByTagName('AlternateContent')->length > 0;
            if ($hasMc) {
                continue;
            }

            $tNodes = $r->getElementsByTagNameNS(self::NS_W, 't');
            foreach ($tNodes as $t) {
                $textNodes[] = $t;
                $full .= $t->textContent;
            }
        }

        if (empty($textNodes) || ! str_contains($full, $search)) {
            return;
        }

        $new = str_replace($search, $replace, $full);
        $textNodes[0]->textContent = $new;
        $textNodes[0]->setAttribute('xml:space', 'preserve');
        for ($i = 1, $len = count($textNodes); $i < $len; $i++) {
            $textNodes[$i]->textContent = '';
        }
    }

    /**
     * Get direct runs text only.
     */
    private function getDirectRunsText(DOMElement $p): string
    {
        $runs = $this->getDirectRuns($p);
        $out = '';
        foreach ($runs as $r) {
            $hasMc = $r->getElementsByTagName('AlternateContent')->length > 0;
            if ($hasMc) {
                continue;
            }

            $tNodes = $r->getElementsByTagNameNS(self::NS_W, 't');
            foreach ($tNodes as $t) {
                $out .= $t->textContent;
            }
        }

        return $out;
    }

    /**
     * @return DOMElement[]
     */
    private function getDirectRuns(DOMElement $p): array
    {
        $runs = [];
        foreach ($p->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'r') {
                $runs[] = $child;
            }
        }

        return $runs;
    }

    private function getDirectRPr(DOMElement $r): ?DOMElement
    {
        foreach ($r->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'rPr') {
                return $child;
            }
        }

        return null;
    }

    private function getDirectPPr(DOMElement $p): ?DOMElement
    {
        foreach ($p->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'pPr') {
                return $child;
            }
        }

        return null;
    }

    /**
     * Specific auto-fill logic for PerjanjianKerjasama.docx (PKS).
     *
     * @param  array<string, string>  $data
     * @param  array<string, mixed>|null  $rab
     */
    private function fillPks(DOMDocument $dom, array $data, ?array $rab): void
    {
        $ns = self::NS_W;

        $hariIndonesia = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $hari = $hariIndonesia[date('l')] ?? 'Selasa';
        $tgl = date('j');
        $bln = $bulanIndonesia[(int) date('n')] ?? 'September';

        $totalBantuan = 69364000;
        $fmtBantuan = 'Rp. '.number_format($totalBantuan, 2, ',', '.').' (Enam Puluh Sembilan Juta Tiga Ratus Enam Puluh Empat Ribu Rupiah)';

        $ps = iterator_to_array($dom->getElementsByTagNameNS($ns, 'p'));
        $seenPihakKesatu = false;

        foreach ($ps as $p) {
            $txt = trim($p->textContent);

            // 1. KEPALA «NAMA_SATUAN_CAPS»
            if (str_contains($txt, '«NAMA_SATUAN_CAPS»') || str_contains($txt, 'NAMA_SATUAN_CAPS')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'KEPALA '.mb_strtoupper($data['nama_sekolah']);
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 2. Opening: Pada hari ini, [hari_PKS] ...
            if (str_contains($txt, 'Pada hari ini') && str_contains($txt, 'kami yang bertanda tangan di bawah ini')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                $newText = "Pada hari ini, $hari tanggal $tgl bulan $bln tahun dua ribu dua puluh enam, kami yang bertanda tangan di bawah ini:";
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = $newText;
                    } else {
                        $t->textContent = '';
                    }
                }

                continue;
            }

            // 3. Identity Blocks
            if (! $seenPihakKesatu) {
                if (str_starts_with($txt, 'Nama') && str_contains($txt, '...')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'Nama      :  '.$data['nama_ppk'];
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }

                if (str_starts_with($txt, 'NIP') && (str_contains($txt, '…') || str_contains($txt, '...'))) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'NIP           :  '.($data['nip_ppk'] ?: '-');
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }

                if (str_starts_with($txt, 'Alamat') && str_contains($txt, '...')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'Alamat     : '.$data['alamat_ppk'];
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }

                if (str_contains($txt, 'yang selanjutnya disebut PIHAK KESATU')) {
                    $seenPihakKesatu = true;

                    continue;
                }
            } else {
                // PIHAK KEDUA
                if (str_starts_with($txt, 'Nama') && str_contains($txt, '...')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'Nama         : '.$data['nama_kepsek'];
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }

                if (str_starts_with($txt, 'NIP/NIK')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'NIP/NIK     : '.($data['nip_kepsek'] ?: '-');
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }

                if (str_starts_with($txt, 'Jabatan') && str_contains($txt, 'Kepala Satuan')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'Jabatan     : Kepala '.$data['nama_sekolah'];
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }

                if (str_starts_with($txt, 'NPSN')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'NPSN         : '.$data['npsn'];
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }

                if (str_starts_with($txt, 'Alamat') && str_contains($txt, '...')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'Alamat      : '.$data['alamat'];
                        } else {
                            $t->textContent = '';
                        }
                    }

                    continue;
                }
            }

            // 4. Pasal 3 - Nilai Bantuan (P77)
            if (str_contains($txt, 'PIHAK KEDUA menerima Dana Bantuan Pengadaan Sarana TIK') && str_contains($txt, 'sebesar xxxxxxx')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $t) {
                    if (str_contains($t->textContent, 'sebesar xxxxxxx')) {
                        $t->textContent = " sebesar $fmtBantuan Berdasarkan Surat Keputusan Pejabat Pembuat Komitmen Direktorat SMP Nomor 0479/PPK/KU-SMP/2026 tanggal 13 Juli 2026 tentang Penerima Bantuan Pemerintah Pengadaan Sarana TIK Tahun Anggaran 2026.";
                    }
                }

                continue;
            }

            // 5. Signature Headers (P132)
            if (str_contains($txt, 'PIHAK KESATU') && str_contains($txt, 'PIHAK KEDUA')) {
                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if ($pPr) {
                    $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                    if ($tabs) {
                        while ($tabs->firstChild) {
                            $tabs->removeChild($tabs->firstChild);
                        }
                    } else {
                        $tabs = $dom->createElementNS($ns, 'w:tabs');
                        $pPr->appendChild($tabs);
                    }
                    $tab = $dom->createElementNS($ns, 'w:tab');
                    $tab->setAttributeNS($ns, 'w:val', 'left');
                    $tab->setAttributeNS($ns, 'w:pos', '5700');
                    $tabs->appendChild($tab);
                }

                continue;
            }

            // 6. Signature Names (P133)
            if (str_contains($txt, 'NAMA PEJABAT PEMBUAT KOMITMEN') && str_contains($txt, 'NAMA KEPALA SEKOLAH')) {
                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                if ($tabs) {
                    while ($tabs->firstChild) {
                        $tabs->removeChild($tabs->firstChild);
                    }
                } else {
                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $pPr->appendChild($tabs);
                }
                $tab = $dom->createElementNS($ns, 'w:tab');
                $tab->setAttributeNS($ns, 'w:val', 'left');
                $tab->setAttributeNS($ns, 'w:pos', '5700');
                $tabs->appendChild($tab);

                $r1 = $dom->createElementNS($ns, 'w:r');
                $rPr1 = $dom->createElementNS($ns, 'w:rPr');
                $b1 = $dom->createElementNS($ns, 'w:b');
                $sz1 = $dom->createElementNS($ns, 'w:sz');
                $sz1->setAttributeNS($ns, 'w:val', '24');
                $rPr1->appendChild($b1);
                $rPr1->appendChild($sz1);
                $r1->appendChild($rPr1);
                $t1 = $dom->createElementNS($ns, 'w:t');
                $t1->textContent = $data['nama_ppk'];
                $r1->appendChild($t1);
                $p->appendChild($r1);

                $rTab = $dom->createElementNS($ns, 'w:r');
                $rTabPr = $dom->createElementNS($ns, 'w:rPr');
                $rTabPr->appendChild($dom->createElementNS($ns, 'w:b'));
                $rTab->appendChild($rTabPr);
                $rTab->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab);

                $r2 = $dom->createElementNS($ns, 'w:r');
                $rPr2 = $dom->createElementNS($ns, 'w:rPr');
                $b2 = $dom->createElementNS($ns, 'w:b');
                $sz2 = $dom->createElementNS($ns, 'w:sz');
                $sz2->setAttributeNS($ns, 'w:val', '24');
                $rPr2->appendChild($b2);
                $rPr2->appendChild($sz2);
                $r2->appendChild($rPr2);
                $t2 = $dom->createElementNS($ns, 'w:t');
                $t2->textContent = $data['nama_kepsek'];
                $r2->appendChild($t2);
                $p->appendChild($r2);

                continue;
            }

            // 7. Signature NIP (P134)
            if (str_starts_with($txt, 'NIP.') && str_contains($txt, 'NIP.')) {
                $runs = [];
                foreach ($p->childNodes as $c) {
                    if ($c->localName === 'r') {
                        $runs[] = $c;
                    }
                }
                foreach ($runs as $r) {
                    $p->removeChild($r);
                }

                $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                if (! $pPr) {
                    $pPr = $dom->createElementNS($ns, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $tabs = $pPr->getElementsByTagNameNS($ns, 'tabs')->item(0);
                if ($tabs) {
                    while ($tabs->firstChild) {
                        $tabs->removeChild($tabs->firstChild);
                    }
                } else {
                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $pPr->appendChild($tabs);
                }
                $tab = $dom->createElementNS($ns, 'w:tab');
                $tab->setAttributeNS($ns, 'w:val', 'left');
                $tab->setAttributeNS($ns, 'w:pos', '5700');
                $tabs->appendChild($tab);

                $r1 = $dom->createElementNS($ns, 'w:r');
                $rPr1 = $dom->createElementNS($ns, 'w:rPr');
                $b1 = $dom->createElementNS($ns, 'w:b');
                $sz1 = $dom->createElementNS($ns, 'w:sz');
                $sz1->setAttributeNS($ns, 'w:val', '24');
                $rPr1->appendChild($b1);
                $rPr1->appendChild($sz1);
                $r1->appendChild($rPr1);
                $t1 = $dom->createElementNS($ns, 'w:t');
                $t1->textContent = 'NIP. '.($data['nip_ppk'] ?: '-');
                $r1->appendChild($t1);
                $p->appendChild($r1);

                $rTab = $dom->createElementNS($ns, 'w:r');
                $rTabPr = $dom->createElementNS($ns, 'w:rPr');
                $rTabPr->appendChild($dom->createElementNS($ns, 'w:b'));
                $rTab->appendChild($rTabPr);
                $rTab->appendChild($dom->createElementNS($ns, 'w:tab'));
                $p->appendChild($rTab);

                $r2 = $dom->createElementNS($ns, 'w:r');
                $rPr2 = $dom->createElementNS($ns, 'w:rPr');
                $b2 = $dom->createElementNS($ns, 'w:b');
                $sz2 = $dom->createElementNS($ns, 'w:sz');
                $sz2->setAttributeNS($ns, 'w:val', '24');
                $rPr2->appendChild($b2);
                $rPr2->appendChild($sz2);
                $r2->appendChild($rPr2);
                $t2 = $dom->createElementNS($ns, 'w:t');
                $t2->textContent = 'NIP. '.($data['nip_kepsek'] ?: '-');
                $r2->appendChild($t2);
                $p->appendChild($r2);

                continue;
            }
        }
    }

    /**
     * Specific auto-fill logic for IdentifikasiAlat.docx (Buku Inventaris / Identifikasi Alat).
     *
     * @param  array<string, string>  $data
     * @param  array<string, mixed>|null  $rab
     */
    private function fillBukuInventaris(DOMDocument $dom, array $data, ?array $rab): void
    {
        $ns = self::NS_W;

        $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
        if ($tables->length === 0) {
            return;
        }

        $tbl = $tables->item(0);
        $rows = $tbl->getElementsByTagNameNS($ns, 'tr');
        if ($rows->length < 7) {
            return;
        }

        $merekTipe = trim((string) ($rab['merek_tipe_laptop'] ?? ''));
        $merek = '';
        $tipe = '';

        if (str_contains($merekTipe, '/')) {
            $parts = explode('/', $merekTipe, 2);
            $merek = trim($parts[0]);
            $tipe = trim($parts[1]);
        } elseif (preg_match('/^(Acer|ASUS|Asus|Lenovo|HP|Dell|Axioo|Advan|Zyrex|Apple|Samsung|Chromebook)\s+(.*)$/i', $merekTipe, $m)) {
            $merek = $m[1];
            $tipe = $m[2];
        } elseif ($merekTipe !== '') {
            $merek = $merekTipe;
            $tipe = (string) ($rab['spesifikasi_ringkas'] ?? 'Standar TIK 2026');
        } else {
            $merek = 'Chromebook / Standar TIK';
            $tipe = 'Standar TIK 2026';
        }

        $jumlahUnit = (int) ($rab['jumlah_unit'] ?? 8);
        $unitEnd = sprintf('%03d', $jumlahUnit);

        // Map row indices in Table 0 to target values and formatting
        $fieldMap = [
            0 => [
                'text' => $data['nama_sekolah'] ?: 'IDENTITAS SEKOLAH',
                'bold' => true,
                'align' => 'center',
                'ind' => '15',
            ],
            2 => [
                'text' => ': LAP-2026-001 s.d. LAP-2026-'.$unitEnd,
                'bold' => false,
                'align' => 'left',
                'ind' => '120',
            ],
            3 => [
                'text' => ': Laptop Pembelajaran (Total '.$jumlahUnit.' Unit)',
                'bold' => false,
                'align' => 'left',
                'ind' => '120',
            ],
            4 => [
                'text' => ': '.$merek,
                'bold' => false,
                'align' => 'left',
                'ind' => '120',
            ],
            5 => [
                'text' => ': '.$tipe,
                'bold' => false,
                'align' => 'left',
                'ind' => '120',
            ],
            6 => [
                'text' => ': Laboratorium Komputer / Ruang TIK',
                'bold' => false,
                'align' => 'left',
                'ind' => '120',
            ],
        ];

        foreach ($fieldMap as $rowIndex => $cfg) {
            $row = $rows->item($rowIndex);
            if (! $row) {
                continue;
            }

            $cells = $row->getElementsByTagNameNS($ns, 'tc');
            if ($cells->length < 2) {
                continue;
            }

            $tc1 = $cells->item(1);
            $p = $tc1->getElementsByTagNameNS($ns, 'p')->item(0);
            if (! $p) {
                $p = $dom->createElementNS($ns, 'w:p');
                $tc1->appendChild($p);
            }

            // Remove existing direct runs
            $runs = [];
            foreach ($p->childNodes as $c) {
                if ($c->localName === 'r') {
                    $runs[] = $c;
                }
            }
            foreach ($runs as $r) {
                $p->removeChild($r);
            }

            $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
            if (! $pPr) {
                $pPr = $dom->createElementNS($ns, 'w:pPr');
                $p->insertBefore($pPr, $p->firstChild);
            }

            $spacing = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
            if (! $spacing) {
                $spacing = $dom->createElementNS($ns, 'w:spacing');
                $pPr->appendChild($spacing);
            }
            $spacing->setAttributeNS($ns, 'w:after', '0');

            if ($cfg['align'] === 'center') {
                $jc = $pPr->getElementsByTagNameNS($ns, 'jc')->item(0);
                if (! $jc) {
                    $jc = $dom->createElementNS($ns, 'w:jc');
                    $pPr->appendChild($jc);
                }
                $jc->setAttributeNS($ns, 'w:val', 'center');
            }

            $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
            if (! $ind) {
                $ind = $dom->createElementNS($ns, 'w:ind');
                $pPr->appendChild($ind);
            }
            $ind->setAttributeNS($ns, 'w:left', $cfg['ind']);

            $r = $dom->createElementNS($ns, 'w:r');
            $rPr = $dom->createElementNS($ns, 'w:rPr');

            if ($cfg['bold']) {
                $b = $dom->createElementNS($ns, 'w:b');
                $rPr->appendChild($b);
            }

            $color = $dom->createElementNS($ns, 'w:color');
            $color->setAttributeNS($ns, 'w:val', '000101');
            $rPr->appendChild($color);

            $sz = $dom->createElementNS($ns, 'w:sz');
            $sz->setAttributeNS($ns, 'w:val', '24');
            $rPr->appendChild($sz);

            $r->appendChild($rPr);

            $t = $dom->createElementNS($ns, 'w:t');
            $t->setAttribute('xml:space', 'preserve');
            $t->textContent = $cfg['text'];
            $r->appendChild($t);

            $p->appendChild($r);
        }
    }

    /**
     * Specific auto-fill logic for PengantarLaporan.docx (Surat Pengantar Laporan Akhir / LPJ).
     *
     * @param  array<string, string>  $data
     */
    private function fillPengantarLaporan(DOMDocument $dom, array $data): void
    {
        $ns = self::NS_W;

        // 1. Header KOP
        $body = $dom->getElementsByTagNameNS($ns, 'body')->item(0);
        if ($body) {
            foreach ($body->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'p') {
                    if (str_contains($child->textContent, 'KOP LEMBAGA SATUAN SMP')) {
                        $tNodes = $child->getElementsByTagNameNS($ns, 't');
                        foreach ($tNodes as $idx => $t) {
                            if ($idx === 0) {
                                $t->textContent = 'KOP '.mb_strtoupper($data['nama_sekolah']);
                            } else {
                                $t->textContent = '';
                            }
                        }
                    }
                }
            }
        }

        // 2. Table 0 (Tanggal & Perihal)
        $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
        if ($tables->length > 0) {
            $tbl = $tables->item(0);
            $rows = $tbl->getElementsByTagNameNS($ns, 'tr');
            if ($rows->length > 0) {
                $rightTc = $rows->item(0)->getElementsByTagNameNS($ns, 'tc')->item(1);
                if ($rightTc) {
                    $ps = $rightTc->getElementsByTagNameNS($ns, 'p');
                    if ($ps->length >= 4) {
                        // P1: Tanggal
                        $p1 = $ps->item(1);
                        $tNodes1 = $p1->getElementsByTagNameNS($ns, 't');
                        foreach ($tNodes1 as $tIdx => $t) {
                            if ($tIdx === 0) {
                                $t->textContent = ': '.$data['tanggal'];
                            } else {
                                $t->textContent = '';
                            }
                        }

                        // P3: Perihal (Laporan Akhir)
                        $p3 = $ps->item(3);
                        $tNodes3 = $p3->getElementsByTagNameNS($ns, 't');
                        foreach ($tNodes3 as $t) {
                            if (str_contains($t->textContent, 'Awal')) {
                                $t->textContent = str_replace('Awal', 'Akhir', $t->textContent);
                            }
                        }
                    }
                }
            }
        }

        // 3. Body paragraphs
        $allPs = iterator_to_array($dom->getElementsByTagNameNS($ns, 'p'));
        $seenTerimaKasih = false;

        foreach ($allPs as $p) {
            $txt = trim($p->textContent);

            // 3.1 Nama
            if (str_starts_with($txt, 'Nama') && str_contains($txt, '…')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Nama                   : '.$data['nama_kepsek'];
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.2 Jabatan
            if (str_starts_with($txt, 'Jabatan') && str_contains($txt, 'Kepala Satuan SMP')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Jabatan                : Kepala '.$data['nama_sekolah'];
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.3 Bertindak atas nama
            if (str_starts_with($txt, 'Bertindak atas nama Satuan Pendidikan SMP')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Bertindak atas nama Satuan Pendidikan '.$data['nama_sekolah'];
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.4 Instansi/Unit
            if (str_starts_with($txt, 'Instansi') && str_contains($txt, 'Unit') && str_contains($txt, '…')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Instansi/Unit       : '.$data['nama_sekolah'];
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.5 Alamat Instansi
            if (str_starts_with($txt, 'Alamat Instansi') && str_contains($txt, '…')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Alamat Instansi   : '.$data['alamat'];
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.5.1 Telepon / HP
            if (str_contains($txt, 'Telepon/HP yang dapat dihubungi') || str_contains($txt, 'Telepon/HP')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = 'Telepon/HP yang dapat dihubungi :  '.($data['no_telepon'] ?: '....................');
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.5.2 RT / RW / No
            if (str_contains($txt, 'Rt.') && str_contains($txt, 'Rw.')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                $rtVal = $data['rt'] ?: '.......';
                $rwVal = $data['rw'] ?: '.......';
                $noVal = $data['nomor_bangunan'] ?: '....................';
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = '      Rt. ['.$rtVal.']  Rw. ['.$rwVal.'] No. '.$noVal;
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.5.3 Desa / Kelurahan
            if (str_contains($txt, 'Desa/Kel.*)')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = '      Desa/Kel.*)          : '.($data['desa_kelurahan'] ?: '................................');
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.5.4 Kecamatan
            if (str_starts_with(trim($txt), 'Kecamatan') && str_contains($txt, '…')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = '      Kecamatan          : '.($data['kecamatan'] ?: '................................');
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.6 Kab/Kota
            if (str_contains($txt, 'Kab/Kota*)') || (str_contains($txt, 'Kab') && str_contains($txt, 'Kota*)'))) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = '      Kab/Kota*)          : '.$data['kabupaten'];
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.7 Provinsi
            if (str_contains($txt, 'Provinsi') && str_contains($txt, '…')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = '      Provinsi                : '.$data['provinsi'];
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.8 E-mail
            if (str_contains($txt, 'E-mail')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $tIdx => $t) {
                    if ($tIdx === 0) {
                        $t->textContent = '      E-mail                   :    '.($data['email_sekolah'] ?: '................................');
                    } else {
                        $t->textContent = '';
                    }
                }
                $this->removeRightIndentsFromNode($p);

                continue;
            }

            // 3.9 Check closing
            if (str_contains($txt, 'kami ucapkan terima kasih')) {
                $seenTerimaKasih = true;

                continue;
            }

            // 4. Signature Block
            if ($seenTerimaKasih) {
                // 4.1 Titimangsa: ………..,……………………….2026
                if (str_contains($txt, '2026') && (str_contains($txt, '…') || str_contains($txt, ','))) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    $titimangsa = ($data['kabupaten'] ? $data['kabupaten'].', ' : '').$data['tanggal'];
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = $titimangsa;
                        } else {
                            $t->textContent = '';
                        }
                    }
                    $this->applySignatureParagraphStyle($p, 40);

                    continue;
                }

                // 4.2 Kepala Satuan SMP
                if (str_contains($txt, 'Kepala Satuan SMP')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'Kepala '.$data['nama_sekolah'];
                        } else {
                            $t->textContent = '';
                        }
                    }
                    $this->applySignatureParagraphStyle($p, 450);

                    continue;
                }

                // 4.3 stempel sekolah dan ttd
                if (str_contains($txt, 'stempel') && str_contains($txt, 'ttd')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'stempel sekolah dan ttd';
                        } else {
                            $t->textContent = '';
                        }
                    }
                    $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
                    if (! $pPr) {
                        $pPr = $dom->createElementNS($ns, 'w:pPr');
                        $p->insertBefore($pPr, $p->firstChild);
                    }
                    $jcNodes = $pPr->getElementsByTagNameNS($ns, 'jc');
                    while ($jcNodes->length > 0) {
                        $pPr->removeChild($jcNodes->item(0));
                    }
                    $jc = $dom->createElementNS($ns, 'w:jc');
                    $jc->setAttributeNS($ns, 'w:val', 'center');
                    $pPr->appendChild($jc);

                    $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
                    if (! $ind) {
                        $ind = $dom->createElementNS($ns, 'w:ind');
                        $pPr->appendChild($ind);
                    }
                    $ind->setAttributeNS($ns, 'w:left', '5000');
                    if ($ind->hasAttributeNS($ns, 'right')) {
                        $ind->removeAttributeNS($ns, 'right');
                    }
                    if ($ind->hasAttribute('w:right')) {
                        $ind->removeAttribute('w:right');
                    }
                    if ($ind->hasAttributeNS($ns, 'firstLine')) {
                        $ind->removeAttributeNS($ns, 'firstLine');
                    }
                    if ($ind->hasAttribute('w:firstLine')) {
                        $ind->removeAttribute('w:firstLine');
                    }

                    $spacing = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
                    if (! $spacing) {
                        $spacing = $dom->createElementNS($ns, 'w:spacing');
                        $pPr->appendChild($spacing);
                    }
                    $spacing->setAttributeNS($ns, 'w:before', '300');
                    $spacing->setAttributeNS($ns, 'w:after', '950');
                    $spacing->setAttributeNS($ns, 'w:line', '240');
                    $spacing->setAttributeNS($ns, 'w:lineRule', 'auto');

                    continue;
                }

                // 4.4 Nama Kepsek
                if ($txt === 'Nama' || $txt === 'Nama:') {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = $data['nama_kepsek'];
                        } else {
                            $t->textContent = '';
                        }
                    }
                    $runs = $p->getElementsByTagNameNS($ns, 'r');
                    foreach ($runs as $r) {
                        $rPr = $r->getElementsByTagNameNS($ns, 'rPr')->item(0);
                        if (! $rPr) {
                            $rPr = $dom->createElementNS($ns, 'w:rPr');
                            $r->insertBefore($rPr, $r->firstChild);
                        }
                        if ($rPr->getElementsByTagNameNS($ns, 'b')->length === 0) {
                            $rPr->appendChild($dom->createElementNS($ns, 'w:b'));
                        }
                    }
                    $this->applySignatureParagraphStyle($p, 20);

                    continue;
                }

                // 4.5 NIP
                if (str_starts_with($txt, 'NIP')) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $tIdx => $t) {
                        if ($tIdx === 0) {
                            $t->textContent = 'NIP. '.($data['nip_kepsek'] ?: '-');
                        } else {
                            $t->textContent = '';
                        }
                    }
                    $this->applySignatureParagraphStyle($p, 0);

                    continue;
                }
            }
        }
    }

    /**
     * Specific auto-fill logic for PerbandinganProduk.docx.
     *
     * @param  array<string, string>  $data
     */
    private function fillPerbandinganProduk(DOMDocument $dom, array $data): void
    {
        $ns = self::NS_W;
        $paragraphs = iterator_to_array($dom->getElementsByTagNameNS($ns, 'p'));
        foreach ($paragraphs as $p) {
            if (str_contains($p->textContent, 'KOP LEMBAGA SATUAN SMP')) {
                $tNodes = $p->getElementsByTagNameNS($ns, 't');
                foreach ($tNodes as $idx => $t) {
                    if ($idx === 0) {
                        $t->textContent = 'KOP '.mb_strtoupper($data['nama_sekolah']);
                    } else {
                        $t->textContent = '';
                    }
                }
            }
        }
    }

    /**
     * Specific auto-fill logic for LaporanPenggunaanDana.docx (Laporan Pertanggungjawaban / LPJ).
     *
     * @param  array<string, string>  $data
     * @param  array<string, mixed>|null  $rab
     */
    private function fillLpj(DOMDocument $dom, array $data, ?array $rab): void
    {
        $ns = self::NS_W;

        // 1. Compact page margins in sectPr
        $sectPr = $dom->getElementsByTagNameNS($ns, 'sectPr')->item(0);
        if ($sectPr) {
            $pgMar = $sectPr->getElementsByTagNameNS($ns, 'pgMar')->item(0);
            if ($pgMar) {
                $pgMar->setAttributeNS($ns, 'w:top', '800');
                $pgMar->setAttributeNS($ns, 'w:bottom', '800');
                $pgMar->setAttributeNS($ns, 'w:left', '1200');
                $pgMar->setAttributeNS($ns, 'w:right', '1000');
            }
        }

        $totalReceived = 69364000;
        $totalUsed = (float) ($rab['total_harga'] ?? 69000000);
        $sisaDana = max(0, $totalReceived - $totalUsed);

        $hariIndonesia = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $hari = $hariIndonesia[date('l')] ?? 'Selasa';
        $tgl = date('j');
        $bln = $bulanIndonesia[(int) date('n')] ?? 'September';
        $blnAngka = date('m');
        $thn = date('Y');

        $setSpacing = function (DOMElement $p, int $before, int $after, int $line = 240) use ($dom, $ns) {
            $pPr = $p->getElementsByTagNameNS($ns, 'pPr')->item(0);
            if (! $pPr) {
                $pPr = $dom->createElementNS($ns, 'w:pPr');
                $p->insertBefore($pPr, $p->firstChild);
            }
            $sp = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
            if (! $sp) {
                $sp = $dom->createElementNS($ns, 'w:spacing');
                $pPr->appendChild($sp);
            }
            $sp->setAttributeNS($ns, 'w:before', (string) $before);
            $sp->setAttributeNS($ns, 'w:after', (string) $after);
            $sp->setAttributeNS($ns, 'w:line', (string) $line);
            $sp->setAttributeNS($ns, 'w:lineRule', 'auto');
        };

        // 2. Body Paragraphs (Bulan & Pada hari ini & Spacings)
        $body = $dom->getElementsByTagNameNS($ns, 'body')->item(0);
        if ($body) {
            foreach ($body->childNodes as $child) {
                if ($child->nodeType !== XML_ELEMENT_NODE || $child->localName !== 'p') {
                    continue;
                }
                $txt = trim($child->textContent);

                if (str_contains($txt, 'LAPORAN PELAKSANAAN')) {
                    $setSpacing($child, 0, 10, 220);
                } elseif (str_contains($txt, 'PERALATAN TIK SMP TAHUN')) {
                    $setSpacing($child, 0, 80, 220);
                } elseif (str_contains($txt, '1.Pelaksanaan Bantuan') || str_contains($txt, '1. Pelaksanaan Bantuan')) {
                    $setSpacing($child, 40, 10, 220);
                } elseif (str_contains($txt, 'Hambatan/Kendala yang dihadapi')) {
                    $setSpacing($child, 0, 10, 220);
                } elseif (str_starts_with($txt, '………………………………………………………………………………………………')) {
                    $setSpacing($child, 0, 20, 220);
                } elseif (str_contains($txt, '2.Penggunaan Dana Bantuan') || str_contains($txt, '2. Penggunaan Dana Bantuan')) {
                    $setSpacing($child, 50, 10, 220);
                } elseif (str_contains($txt, 'PENCATATAN PENGGUNAAN DANA BANTUAN') && str_contains($txt, 'Bulan')) {
                    $tNodes = $child->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $t) {
                        if (str_contains($t->textContent, '…..') || str_contains($t->textContent, 'Bulan :')) {
                            $t->textContent = 'Bulan  : '.$bln.' '.$thn;
                        }
                    }
                    $setSpacing($child, 0, 10, 220);
                } elseif (str_contains($txt, 'Tahun : 2026')) {
                    $setSpacing($child, 0, 30, 220);
                } elseif (str_starts_with($txt, 'Pada hari ini') && str_contains($txt, 'tanggal') && str_contains($txt, 'bulan')) {
                    $newText = "Pada hari ini : $hari tanggal $tgl bulan $bln tahun";
                    $tNodes = $child->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $idx => $t) {
                        if ($idx === 0) {
                            $t->textContent = $newText;
                        } else {
                            $t->textContent = '';
                        }
                    }
                    $setSpacing($child, 50, 0, 220);
                } elseif (str_contains($txt, 'Buku Pencatatan Dana Bantuan ditutup')) {
                    $setSpacing($child, 0, 30, 220);
                } elseif ($txt === '') {
                    $setSpacing($child, 0, 0, 20);
                }
            }
        }

        // 3. Table 0 (Pencatatan Transaksi)
        $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
        if ($tables->length > 0) {
            $tbl0 = $tables->item(0);
            $rows0 = $tbl0->getElementsByTagNameNS($ns, 'tr');

            $setCellText = function (DOMElement $tc, string $val, string $align = 'left') use ($dom, $ns) {
                $p = $tc->getElementsByTagNameNS($ns, 'p')->item(0);
                if (! $p) {
                    $p = $dom->createElementNS($ns, 'w:p');
                    $tc->appendChild($p);
                }
                while ($p->firstChild) {
                    $p->removeChild($p->firstChild);
                }
                $pPr = $dom->createElementNS($ns, 'w:pPr');
                $spacing = $dom->createElementNS($ns, 'w:spacing');
                $spacing->setAttributeNS($ns, 'w:after', '0');
                $spacing->setAttributeNS($ns, 'w:line', '220');
                $spacing->setAttributeNS($ns, 'w:lineRule', 'auto');
                $pPr->appendChild($spacing);
                if ($align !== 'left') {
                    $jc = $dom->createElementNS($ns, 'w:jc');
                    $jc->setAttributeNS($ns, 'w:val', $align);
                    $pPr->appendChild($jc);
                }
                $p->appendChild($pPr);

                $r = $dom->createElementNS($ns, 'w:r');
                $rPr = $dom->createElementNS($ns, 'w:rPr');
                $sz = $dom->createElementNS($ns, 'w:sz');
                $sz->setAttributeNS($ns, 'w:val', '19');
                $rPr->appendChild($sz);
                $r->appendChild($rPr);

                $t = $dom->createElementNS($ns, 'w:t');
                $t->setAttribute('xml:space', 'preserve');
                $t->textContent = $val;
                $r->appendChild($t);
                $p->appendChild($r);
            };

            // Row 1: Penerimaan Dana
            if ($rows0->length > 1) {
                $c1 = $rows0->item(1)->getElementsByTagNameNS($ns, 'tc');
                if ($c1->length >= 7) {
                    $setCellText($c1->item(0), '1', 'center');
                    $setCellText($c1->item(1), '13/07/2026', 'center');
                    $setCellText($c1->item(2), 'SP2D-0479', 'center');
                    $setCellText($c1->item(3), 'Penerimaan Dana Bantuan Peralatan TIK SMP 2026');
                    $setCellText($c1->item(4), number_format($totalReceived, 0, ',', '.'), 'right');
                    $setCellText($c1->item(5), '-', 'center');
                    $setCellText($c1->item(6), number_format($totalReceived, 0, ',', '.'), 'right');
                }
            }

            // Row 2: Pengeluaran Pembelian Laptop TIK
            if ($rows0->length > 2) {
                $c2 = $rows0->item(2)->getElementsByTagNameNS($ns, 'tc');
                if ($c2->length >= 7) {
                    $merek = $rab['merek_tipe_laptop'] ?? 'Laptop Standar TIK';
                    $qty = $rab['jumlah_unit'] ?? 8;
                    $setCellText($c2->item(0), '2', 'center');
                    $setCellText($c2->item(1), $tgl.'/'.$blnAngka.'/'.$thn, 'center');
                    $setCellText($c2->item(2), 'KWT-01/SIPLah', 'center');
                    $setCellText($c2->item(3), 'Pembelian '.$qty.' Unit '.$merek.' via SIPLah');
                    $setCellText($c2->item(4), '-', 'center');
                    $setCellText($c2->item(5), number_format($totalUsed, 0, ',', '.'), 'right');
                    $setCellText($c2->item(6), number_format($sisaDana, 0, ',', '.'), 'right');
                }
            }

            // Row 3: Total / Jumlah
            if ($rows0->length > 3) {
                $c3 = $rows0->item(3)->getElementsByTagNameNS($ns, 'tc');
                if ($c3->length >= 6) {
                    $setCellText($c3->item(3), number_format($totalReceived, 0, ',', '.'), 'right');
                    $setCellText($c3->item(4), number_format($totalUsed, 0, ',', '.'), 'right');
                    $setCellText($c3->item(5), number_format($sisaDana, 0, ',', '.'), 'right');
                }
            }
        }

        // 4. Table 1 (Closing Statement & Signatures)
        if ($tables->length > 1) {
            $tbl1 = $tables->item(1);

            foreach ($tbl1->getElementsByTagNameNS($ns, 'p') as $p) {
                $txt = trim($p->textContent);
                $targetAmount = null;

                if (str_starts_with($txt, 'Jumlah Dana Bantuan Yang Diterima')) {
                    $targetAmount = number_format($totalReceived, 2, ',', '.');
                } elseif (str_starts_with($txt, 'Total Pengeluaran')) {
                    $targetAmount = number_format($totalUsed, 2, ',', '.');
                } elseif (str_starts_with($txt, 'Saldo Bantuan')) {
                    $targetAmount = number_format($sisaDana, 2, ',', '.');
                } elseif (str_starts_with($txt, 'Saldo Bank')) {
                    $targetAmount = number_format($sisaDana, 2, ',', '.');
                } elseif (str_starts_with($txt, 'Saldo Kas Tunai')) {
                    $targetAmount = '0,00';
                } elseif (str_starts_with($txt, 'Jumlah') && str_contains($txt, 'Rp.')) {
                    $targetAmount = number_format($sisaDana, 2, ',', '.');
                }

                if ($targetAmount !== null) {
                    $tNodes = $p->getElementsByTagNameNS($ns, 't');
                    foreach ($tNodes as $t) {
                        if (str_contains($t->textContent, 'Rp.')) {
                            $t->textContent = ' : Rp. '.$targetAmount;
                        }
                    }
                    $setSpacing($p, 0, 0, 210);
                }
            }

            // Build 2-column signature block in Table 1
            $cellWithSig = null;
            foreach ($tbl1->getElementsByTagNameNS($ns, 'tc') as $tc) {
                if (str_contains($tc->textContent, 'Mengetahui')) {
                    $cellWithSig = $tc;
                    break;
                }
            }

            if ($cellWithSig) {
                $ps = iterator_to_array($cellWithSig->getElementsByTagNameNS($ns, 'p'));
                foreach ($ps as $p) {
                    $txt = trim($p->textContent);
                    if (str_contains($txt, 'Mengetahui') || str_contains($txt, 'Kepala SMP') || str_contains($txt, 'Bendahara')) {
                        $p->parentNode->removeChild($p);
                    }
                }

                $createTwoColP = function (string $leftText, string $rightText, bool $bold = false, int $spBefore = 0, int $spAfter = 10, int $szVal = 21) use ($dom, $ns) {
                    $p = $dom->createElementNS($ns, 'w:p');
                    $pPr = $dom->createElementNS($ns, 'w:pPr');

                    $tabs = $dom->createElementNS($ns, 'w:tabs');
                    $tab = $dom->createElementNS($ns, 'w:tab');
                    $tab->setAttributeNS($ns, 'w:val', 'left');
                    $tab->setAttributeNS($ns, 'w:pos', '5200');
                    $tabs->appendChild($tab);
                    $pPr->appendChild($tabs);

                    $spacing = $dom->createElementNS($ns, 'w:spacing');
                    $spacing->setAttributeNS($ns, 'w:before', (string) $spBefore);
                    $spacing->setAttributeNS($ns, 'w:after', (string) $spAfter);
                    $spacing->setAttributeNS($ns, 'w:line', '220');
                    $spacing->setAttributeNS($ns, 'w:lineRule', 'auto');
                    $pPr->appendChild($spacing);
                    $p->appendChild($pPr);

                    // Left Run
                    $r1 = $dom->createElementNS($ns, 'w:r');
                    $rPr1 = $dom->createElementNS($ns, 'w:rPr');
                    $sz1 = $dom->createElementNS($ns, 'w:sz');
                    $sz1->setAttributeNS($ns, 'w:val', (string) $szVal);
                    $rPr1->appendChild($sz1);
                    if ($bold) {
                        $rPr1->appendChild($dom->createElementNS($ns, 'w:b'));
                    }
                    $r1->appendChild($rPr1);
                    $t1 = $dom->createElementNS($ns, 'w:t');
                    $t1->textContent = $leftText;
                    $r1->appendChild($t1);
                    $p->appendChild($r1);

                    // Tab Run
                    $rTab = $dom->createElementNS($ns, 'w:r');
                    $rTab->appendChild($dom->createElementNS($ns, 'w:tab'));
                    $p->appendChild($rTab);

                    // Right Run
                    $r2 = $dom->createElementNS($ns, 'w:r');
                    $rPr2 = $dom->createElementNS($ns, 'w:rPr');
                    $sz2 = $dom->createElementNS($ns, 'w:sz');
                    $sz2->setAttributeNS($ns, 'w:val', (string) $szVal);
                    $rPr2->appendChild($sz2);
                    if ($bold) {
                        $rPr2->appendChild($dom->createElementNS($ns, 'w:b'));
                    }
                    $r2->appendChild($rPr2);
                    $t2 = $dom->createElementNS($ns, 'w:t');
                    $t2->textContent = $rightText;
                    $r2->appendChild($t2);
                    $p->appendChild($r2);

                    return $p;
                };

                $titimangsa = ($data['kabupaten'] ? $data['kabupaten'].', ' : '').$data['tanggal'];

                $cellWithSig->appendChild($createTwoColP('Mengetahui,', $titimangsa, false, 80, 20));
                $cellWithSig->appendChild($createTwoColP('Kepala '.$data['nama_sekolah'], 'Bendahara', false, 0, 150));
                $cellWithSig->appendChild($createTwoColP('stempel sekolah dan ttd', 'ttd', false, 0, 800));
                $cellWithSig->appendChild($createTwoColP($data['nama_kepsek'], $data['nama_bendahara'], true, 0, 10));
                $cellWithSig->appendChild($createTwoColP('NIP. '.($data['nip_kepsek'] ?: '-'), 'NIP. '.($data['nip_bendahara'] ?: '-'), false, 0, 0));
            }
        }
    }

    private function tanggalIndonesia(): string
    {
        $now = Carbon::now('Asia/Jakarta');
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $now->day.' '.$bulan[$now->month].' '.$now->year;
    }
}
