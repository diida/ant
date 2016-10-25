# ANT 框架
设计目标：小，够用，规范
通过composer安装

## 弱耦合
弱耦合是这个框架的核心设计原则，除了entry，其他部分都尽量减少依赖关系。虽然我们也不可能单独使用某个文件，但是耦合越低，错误就越容易解决。

## restful
Ant框架基于restful架构，使用GET POST PUT DELETE 实现 查、增、改、删

## 入口
接受2种url参数来访问固定的地址，其中带有下划线的链接，控制器和父级共享，这提供一种良好的封装逻辑结构的方法。第五个是一种无需path参数的方案，
一般需要服务器配置来配合使用。

1. GET index.php?path=game/user
2. GET index.php?path=game/user/_password
3. GET index.php?path=game/user/friend
4. GET index.php?path=game/user/friend/_group&group_id=1
5. GET index.php?game/user/friend/_group&group_id=1

### 第四个链接会让人觉得有点不爽，在正式环境重写后，应该是这样
1. GET /game/user/friend/_group?group_id=1

### 重写的原理
如果你的默认入口设置了index.php，基于下面原理1，你可以不做任何重写设置，但是如果有参数就一定要用了，所以第3种重写方案应该是普遍的。

1. GET /game/user/friend => /index.php?game/user/friend
2. GET /game/user/friend => /index.php?path=game/user/friend
3. GET /game/user/friend?x=1 => /index.php?path=game/user/friend&x=1

在nginx中，你需要加上这样的代码
```
rewrite ^/(.*)?(.*)$ /index.php?path=$1&$2
rewrite ^/(.*)$ /index.php?path=$1
```
## url参数规范
Ant框架不提倡自定义参数格式。例如index.php?a=1&b=2，通过自定义为index-a-1-b-2。尽量使用正常的url参数书写方式。

当需要一个短的、体验良好的url格式时，一般需要在代码或者nginx或者apache中设置。

但是很多时候开发人员还是希望有这个功能，下面就介绍一些方案。

### 数字ID
但是为了方便起见，比如下面这个

* /user/1234567

我们假设它指向一个用户的个人主页，由于number不能作为类名或者方法名，所以当框架遇到最后一个是数字时，会将数字默认丢到
reauest::get('id') 中，由于这样一个需求很常见，直接由框架实现。

### 更复杂需求:

* /album/fid/1231/photo_id

这就需要代码实现，不能直接由框架处理了，因为框架无法知道fid的具体含义

### 下划线隔离
如果有一个下划线作为隔离，那框架可以识别访问路径的终止位置，第二个例子中，数字也成为了隔离参数

* /album/photo/_/1231 =>  /album/photo?id=1231 => /album/photo/1231
* /album/photo/_/1231/fid/3 =>  /album/photo?id=1231&fid=3 => /album/photo/1231/fid/3
* /album/photo/_pic/123 => /album/photo/_pic?id=123
* /album/photo/_pic/1234/fid/2 => /album/photo/_pic?id=1234&fid=2
* /album/photo/_pic/pid/1234/fid/2 => /album/photo/_pic?pid=1234&fid=2

### 希望实现以资源ID作为路由
需求是这样，希望某个用户的资源都以他ID为路径之一
* user/1234567/friend
* user/1234567/account/password/_change
* ...

实际解析完这些参数后，控制器入口是在rs/user.php，user的exec函数可以重写，在它的第三个参数中

* ['friend']
* ['account','password','_change']

你可以利用它构建自己的路由规则，此时get数据为

* []
* ['account'=>'password']

注意此时get中的值可能就不是你想要的了，你得自己处理get，通过下面的函数，将数据放到request可访问的范围中，这样内部处理逻辑就可以保持一致，
而不会感觉到路由和框架规则被改变。

request::get('uid')->setDefault('1234567')

这样user就成为了一个路由的入口了