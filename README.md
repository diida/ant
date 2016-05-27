# ANT 框架最希望达到就是小，够用

## 弱耦合
弱耦合是这个框架的核心设计原则，除了entry，其他部分都尽量减少依赖关系。虽然我们也不可能单独使用某个文件，但是耦合越低，错误就越容易解决。

## restful
ant框架基于restful架构，使用GET POST PUT DELETE 实现 查、增、改、删

## 入口
接受2种url参数来访问固定的地址，带有下划线的链接，控制器和父级共享，这提供一种良好的封装逻辑的结构
1. GET index.php?path=game/user
2. GET index.php?path=game/user/_password
3. GET index.php?path=game/user/friend
4. GET index.php?path=game/user/friend/_group&group_id=1

### 第四个链接会让人觉得有点不爽，在正式环境重写后，应该是这样
1. GET /game/user/friend/_group?group_id=1

### 重写的原理
如果你的默认入口设置了index.php，基于原理1，你可以不做任何重写设置

1. GET /game/user/friend => /index.php?game/user/friend
2. GET /game/user/friend => /index.php?path=game/user/friend
2. GET /game/user/friend?x=1 => /index.php?path=game/user/friend&x=1

## url参数规范
ant框架不提倡自定义参数格式。例如index.php?a=1&b=2，通过自定义为index-a-1-b-2。当需要一个短的、体验良好的url格式时
请在代码或者nginx或者apache中设置<br>
但是为了方便起见，比如下面这个<br>
/user/1234567 指向一个用户的个人主页，由于number不能作为类名或者方法名，所以当框架遇到最后一个是数字时，会将数字默认丢到
reauest::get('id') 中，由于这样一个需求很常见，直接由框架实现。更复杂需求如下:<br>
```url
/album/fid/1231/photo_id
```
就需要代码实现，不能直接由框架处理了，但是此时框架会吧photo_id放到reauest::get('id') 中,然后在处理1231的时候报错。<br>
如果有一个下划线作为隔离，那框架还是可以识别的，下面是利用下划线隔离路径和参数的例子
* /album/photo/_/1231 =>  /album/photo?id=1231 => /album/photo/1231
* /album/photo/_/1231/fid/3 =>  /album/photo?id=1231&fid=3
* /album/photo/_pic/123 => /album/photo/_pic?id=123
* /album/photo/_pic/1234/fid/2 => /album/photo/_pic?id=1234&fid=2
* /album/photo/_pic/pid/1234/fid/2 => /album/photo/_pic?pid=1234&fid=2