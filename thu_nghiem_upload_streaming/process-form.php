<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Request method must be POST');
}

print_r($_FILES);