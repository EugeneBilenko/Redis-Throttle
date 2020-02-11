<?php

/**
 * Class ThrottleTest
 */
class ThrottleTest extends \PHPUnit\Framework\TestCase
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
	 * Remove test keys from redis
	 */
	protected function tearDown(): void
	{
		$redis = new Redis();
		$redis->connect($this->host, $this->port);

		foreach ($this->dataProvider() as $key => $values) {
			$redis->del($values[0]);
		}

		unset($redis);
	}

	/**
	 * Testing overload
	 * Failure on tries more than attempts allowed on time interval
	 *
	 * @dataProvider dataProvider
	 */
	public function testOverload($key, $attempts, $interval, $tries)
	{
		$result = 0;
		$redis = new Throttle($key, $attempts, $interval);

		try {

			for ($i = 0; $i < $tries; $i++) {
				$redis->execute();
			}
		} catch (ThrottleException $e) {
			$result = 1;
		}

		$this->assertEquals(0, $result);
	}

	/**
	 * 'Name' => [
	 *      'key' : String,
	 *      'attempts allowed' : Integer,
	 *      'time interval' : Integer(seconds),
	 *      'tries' : Integer(count action called)
	 * ];
	 * @return array
	 */
	public function dataProvider()
	{
		return [
			'overload' => ['test1', 500, 10, 1000],
			'equals' => ['test2', 1000, 10, 1000],
			'normal' => ['test3', 1500, 10, 1000],
		];
	}
}
