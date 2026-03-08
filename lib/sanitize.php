<?php
// Very small whitelist-based sanitizer. Not a replacement for a robust library.

function sanitize_html_whitelist(string $html): string
{
    $allowed_tags = ['p', 'br', 'b', 'strong', 'i', 'em', 'a', 'h1', 'h2'];
    // strip all tags except allowed
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $body = $doc->getElementsByTagName('body')->item(0);
    $out = '';
    foreach ($body->childNodes as $node) {
        $out .= process_node($node, $allowed_tags);
    }
    return $out;
}

function process_node($node, $allowed_tags)
{
    if ($node->nodeType === XML_TEXT_NODE) {
        return htmlspecialchars($node->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    if ($node->nodeType !== XML_ELEMENT_NODE) {
        return '';
    }
    $tag = strtolower($node->nodeName);
    if (!in_array($tag, $allowed_tags, true)) {
        // unwrap children
        $s = '';
        foreach ($node->childNodes as $c)
            $s .= process_node($c, $allowed_tags);
        return $s;
    }
    $attrs = '';
    if ($tag === 'a' && $node->hasAttributes()) {
        $href = trim($node->getAttribute('href'));
        $allowedProtocols = ['http://', 'https://', 'mailto:', 'tel:', '/'];
        $isValid = false;
        foreach ($allowedProtocols as $proto) {
            if (stripos($href, $proto) === 0) {
                $isValid = true;
                break;
            }
        }
        if ($isValid) {
            $attrs = ' href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" rel="noopener noreferrer"';
        }
    }
    $inner = '';
    foreach ($node->childNodes as $c)
        $inner .= process_node($c, $allowed_tags);
    return "<{$tag}{$attrs}>{$inner}</{$tag}>";
}
?>