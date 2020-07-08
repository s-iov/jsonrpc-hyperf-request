# hyperf-jsonrpc-request
**description : hyperf客户端调用swoft-jsonrpc服务**



## 需要添加监听

```
\Clitoser\Clitoser\Listener\AddConsumerDefinitionListener::class,
```



## 调用方法：

**引入命名空间**

```
use Clitoser\Clitoser\ConnectToSer;
```

```
// 服务名称 name （consumers配置中的name值）
// 方法 method (swoft服务中services的方法名称)
// 参数 params （数组形式的参数）
ConnectToSer::getInstance()->get('name', 'method', 'params');
$res = ConnectToSer::getInstance()->client();
```

**services配置**
```
'consumers' => [
	[
	    // 服务名称 必填项
	    'name' => 'demo',	
	    // 如果配置的为swoft jsonrpc服务则为必填项
	    'rpcserver' => 'swoft',
	    // interface 必填项
	    'service' => \App\Rpc\Lib\Auth\AuthManagerInterface::class,
	    // 这个消费者要从哪个服务中心获取节点信息，如不配置则不会从服务中心获取节点信息 非必填
	    'registry' => [
	        'protocol' => 'consul',
	        'address' => 'http://127.0.0.1:8500',
	    ],
	    // 如果没有指定上面的 registry 配置，即为直接对指定的节点进行消费，通过下面的 nodes 参数来配置服务提供者的节点信息 必填项
	    'nodes' => [
	        ['host' => '172.26.130.178', 'port' => 8099],
	    ],
	],	
]
```