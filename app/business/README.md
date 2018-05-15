# gateway business相关  
此文件夹下放置需要通过gateway发送给其他business-worker进行业务查询等逻辑的项目文件  
1. gateway可以部署在其他机器上  
2. business可以部署在其他机器上  
3. gateway不会有压力，所以不需要分布式处理  
4. business可以分布式部署提高效率  

>##client如何实现分布式  
1. client加入同一分组  
2. business将消息通过gateway发送给分组内所有客户端  
3. client自己判断是否是自己关注的消息