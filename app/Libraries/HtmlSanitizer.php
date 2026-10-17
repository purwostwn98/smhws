<?php

namespace App\Libraries;

/**
 * Sanitasi HTML whitelist untuk isi artikel (hasil editor Quill).
 * Tag di luar daftar dibuka (isinya dipertahankan), kecuali tag berbahaya yang dibuang seluruhnya.
 */
class HtmlSanitizer
{
    private const TAG_BOLEH = [
        'p' => [], 'br' => [], 'h2' => [], 'h3' => [], 'h4' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [],
        'a'   => ['href'],
        'img' => ['src', 'alt'],
    ];

    private const TAG_BUANG = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'svg', 'math'];

    public static function bersihkan(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') return '';

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        if (! $root) return '';
        self::listQuill($doc, $root);
        self::proses($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        // Quill memberi paragraf kosong "<p><br></p>" — rapikan di akhir
        return trim(preg_replace('#(<p><br></p>\s*)+$#', '', $out));
    }

    /**
     * Quill 2 menyimpan semua daftar sebagai <ol><li data-list="bullet|ordered">.
     * Ubah <ol> yang berisi bullet menjadi <ul> sebelum atribut dibuang.
     */
    private static function listQuill(\DOMDocument $doc, \DOMElement $root): void
    {
        foreach (iterator_to_array($root->getElementsByTagName('ol')) as $ol) {
            $li = null;
            foreach ($ol->childNodes as $c) {
                if ($c instanceof \DOMElement && strtolower($c->tagName) === 'li') { $li = $c; break; }
            }
            if (! $li || $li->getAttribute('data-list') !== 'bullet') continue;

            $ul = $doc->createElement('ul');
            while ($ol->firstChild) {
                $ul->appendChild($ol->firstChild);
            }
            $ol->parentNode->replaceChild($ul, $ol);
        }
    }

    private static function proses(\DOMNode $node): void
    {
        // Salin daftar anak karena akan dimodifikasi
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);
                continue;
            }
            if (! $child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::TAG_BUANG, true)) {
                $node->removeChild($child);
                continue;
            }

            self::proses($child);

            if (! array_key_exists($tag, self::TAG_BOLEH)) {
                // Buka tag: pindahkan isi ke induk
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $nama = strtolower($attr->name);
                if (! in_array($nama, self::TAG_BOLEH[$tag], true)) {
                    $child->removeAttribute($attr->name);
                    continue;
                }
                if (in_array($nama, ['href', 'src'], true) && ! preg_match('#^https?://#i', trim($attr->value))) {
                    $child->removeAttribute($attr->name);
                }
            }

            if ($tag === 'a' && $child->hasAttribute('href')) {
                $child->setAttribute('target', '_blank');
                $child->setAttribute('rel', 'noopener noreferrer nofollow');
            }
            if ($tag === 'img' && ! $child->hasAttribute('src')) {
                $node->removeChild($child);
            }
        }
    }
}
