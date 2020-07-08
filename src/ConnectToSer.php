<?php
declare(strict_types=1);

namespace Clitoser\Clitoser;

use Hyperf\Consul\KV;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Consul\Agent;

class ConnectToSer
{
    private $nodeaddr;
    private $interface;
    private $consuladdr;
    private $name;
    private $method;
    private $params;
    private static $instance;

    public function getArgs($nodeaddr, $interface, $consuladdr){
        $this->nodeaddr = $nodeaddr;
        $this->interface = $interface;
        $this->consuladdr = $consuladdr;
    }

    public function get($name, $method, $params){
        $this->name = $name;
        $this->method = $method;
        $this->params = $params;
    }

    public static function getInstance()
    {
        if (isset($instance)) {
            return $instance;
        }
        if (!isset(self::$instance) || (self::$instance === null)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function client(){
        $container = ApplicationContext::getContainer();
        $clientFactory = $container->get(ClientFactory::class);

        $addr = '';
        $consulserver = $this->consuladdr[$this->name];
        $agent = new Agent(function () use ($clientFactory, $consulserver) {
            return $clientFactory->create([
                'base_uri' => $consulserver,
            ]);
        });

        //处理健康检查
        try{
            $arr = $agent->checks()->json();
            if (array_pop($arr)['Status'] == 'passing'){
                $addr = array_pop($arr)['Address'].':'.array_pop($arr)['Port'];
            } else {
                $addr = $this->nodeaddr[$this->name];
            }
        }catch (\Exception $e){
            $addr = $this->nodeaddr[$this->name];
        }

        //tcp连接
        $fp = stream_socket_client($addr, $errno, $errstr);
        if (!$fp) {
            throw new \Exception("stream_socket_client fail errno={$errno} errstr={$errstr}");
        }

        //传入数据
        $data = [
            'interface' => $this->interface[$this->name],
            'version'   => '1.0.0',
            'method'    => $this->method,
            'params'    => $this->params,
            'logid'     => uniqid(),
            'spanid'    => 0,
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE)."\r\n";
        fwrite($fp, $data);
        $result = fread($fp, 1024);
        fclose($fp);
        return $result;
    }

}