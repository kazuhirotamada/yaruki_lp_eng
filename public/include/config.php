<?php
    function loadConstants() {
        static $cache = null; // ← 一度だけ読み込むキャッシュ

        if ($cache !== null) return $cache;

        $path = __DIR__ . '/../libs/constants.json';
        if (!file_exists($path)) {
            throw new Exception("constants.json not found: $path");
        }

        $data = file_get_contents($path);
        $cache = json_decode($data, true);

        return $cache;
    }

    function loadPageMetas() {
        static $cache = null; // ← 一度だけ読み込むキャッシュ

        if ($cache !== null) return $cache;

        $path = __DIR__ . '/../libs/pageMetas.json';
        if (!file_exists($path)) {
            throw new Exception("pageMetas.json not found: $path");
        }

        $data = file_get_contents($path);
        $cache = json_decode($data, true);

        return $cache;
    }
?>