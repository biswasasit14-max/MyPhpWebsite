<?php
/**
 * Helper functions
 */

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url, int $code = 302): void {
    header('Location: ' . $url, true, $code);
    exit;
}
