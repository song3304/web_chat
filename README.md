# web-chat

聊天对话项目  

# 目录结构
```
app
|----business               gateway&business处理单元，属于另一个系统
        |--model                    数据库操作模型抽象
        |--msg                      消息处理
        |--Events.php               事务逻辑
        |--MsgHandler.php           事务处理逻辑调度
        |--MsgIds.php               消息事务编码
        |--start_businessworker.php 启动business
        |--start_gateway.php        启动gateway
        |--start_register.php       启动注册中心
|----conf                   配置文件
|----dbrequest              需要数据库操作的请求和响应处理
|----message                消息处理，不需要数据库操作的消息处理
|----ChatServer.php         主服务
|----MessageHandler.php     与gateway相关处理单元通信消息调度
|----TcpClient.php          与gateway通信客户端
|----test                   测试目录
|----autoload.php           自动加载
|----start-business.php     快速启动business相关
|----start-chat-server.php  启动chatserver主程序
|----start.php              快速启动chatserver主程序
```

## 业务简述  
一、启动gateway逻辑处理单元  
    1、监听业务请求  
    2、处理业务请求并返回    
二、启动chatserver   
    1、启动一个chatserver   
    2、chatserserver初始化   
    3、chatserver连接gateway  
    4、chatserver处理业务请求  
### ChatServer  
    

