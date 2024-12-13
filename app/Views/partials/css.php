<?php
foreach ($css as $style) {
    echo '<link rel="stylesheet" href="' . base_url('plugins/' . $style . '.css') . '">';
}
?>