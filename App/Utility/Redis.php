<?php
namespace App\Utility;

class Redis
{
    protected static $instance = null;

    protected $options = [
        'host'       => '10.240.0.63',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
    ];

    /**
     * 构造函数
     * @param array $options 参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * 连接Redis
     * @return void
     */
    protected function connect()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new \Redis;
            if ($this->options['persistent']) {
                self::$instance->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
            } else {
                self::$instance->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            }

            if ('' != $this->options['password']) {
                self::$instance->auth($this->options['password']);
            }

            if (0 != $this->options['select']) {
                self::$instance->select($this->options['select']);
            }
        }
    }

    /**
     * 获取连接句柄
     * @return object Redis
     */
    public function handler()
    {
        $this->connect();
        return self::$instance;
    }
}