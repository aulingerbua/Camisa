<?php
trait Messages {
	protected $err_msg = NULL;
	protected $conf_msg = NULL;
	protected $warn_msg = NULL;
	protected $not_msg = NULL;
	public function error() {
		if ($this->err_msg) {
			echo "<p class='alert'>$this->err_msg</p>";
		}
	}
	public function getError () {
		return $this->err_msg;
	}
	public function confirm() {
		if ($this->conf_msg) {
			echo "<div class='confirm'>$this->conf_msg</div>";
		}
	}
	public function warning() {
		if ($this->warn_msg) {
			echo "<p class='alert'>$this->warn_msg</p>";
		}
	}
	public function notice() {
		if ($this->not_msg) {
			echo "<p class='alert'>$this->not_msg</p>";
		}
	}
	public function allMessages() {
		self::error();
		self::confirm();
		self::warning();
		self::notice();
	}
}