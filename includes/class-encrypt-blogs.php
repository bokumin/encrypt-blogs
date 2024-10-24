<?php
class Encrypt_Blogs {
    private $admin;
    private $block;
    private $encryptor;

    public function __construct() {
        $this->admin = new Encrypt_Blogs_Admin();
        $this->block = new Encrypt_Blogs_Block();
        $this->encryptor = new Encrypt_Blogs_Encryptor();
    }

    public function run() {
        $this->admin->init();
        $this->block->init();
        $this->encryptor->init();
    }
}
