<?php
foreach ($js as $script) {
    echo '<script src="' . base_url('plugins/' . $script . '.js') . '"></script>';
}
?>