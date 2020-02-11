<?php
require_once "ThrottleException.php";

/**
 * Class Throttle
 */
class Throttle
{
	/**
	 * Redis host
	 * @var string
	 */
	private $host = '127.0.0.1';

	/**
	 * Redis port
	 * @var int
	 */
	private $port = 6379;

	/**
	 * Redis instance
	 * @var Redis
	 */
	private $redis;

	/**
	 * Redis key
	 * @var
	 */
	public $key;

	/**
	 * Attempts allowed
	 * @var integer
	 */
	public $attempt;

	/**
	 * Time interval in seconds
	 * @var integer
	 */
	public $interval;

	/**
	 * Throttle constructor.
	 * @param $key
	 * @param $attempt
	 * @param $interval
	 */
	public function __construct($key, $attempt, $interval)
	{
		$this->key = $key;
		$this->attempt = $attempt;
		$this->interval = $interval;
		$this->redisConnect();
	}

	/**
	 * Setup redis connection
	 */
	private function redisConnect()
	{
		$this->redis = new Redis();
		$this->redis->connect($this->host, $this->port);
	}

	/**
	 * Set attempts key
	 * Check attempts
	 * Throw ThrottleException on limit attempts exceed
	 * @throws ThrottleException
	 */
	private function increaseAttempts()
	{
		if (!$attempts = $this->redis->get($this->key)) {
			$this->redis->set($this->key, 1, $this->interval);
		} else {
			if ($attempts >= $this->attempt) {
				throw new ThrottleException('Limit attempts');
			}
			$this->redis->incr($this->key);
		}
	}

	/**
	 * Execute action
	 * @throws ThrottleException
	 */
	public function execute()
	{
		$this->increaseAttempts();
	}
}
