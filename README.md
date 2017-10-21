### 自建lnmp+redis



| 目录位置       | 功能        | 备注                                       |
| ---------- | --------- | ---------------------------------------- |
| /web/data  | 存放项目      |                                          |
| /web/logs  | 存放日志      | 内部包含 nginx php 这两个目录                     |
| /web/conf  | 存放配置文件    | nginx路径暂定为/web/conf/nginx/conf.d;nginx配置需要这样写fastcgi_pass   php:9000 |
| /web/mysql | mysql数据目录 |                                          |
| /web/redis | redis数据目录 |                                          |



## 启动方法

docker-compose up -d

