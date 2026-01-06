

### Dockerfile 与 docker-compose.yml 的关系

- **Dockerfile (构建阶段/精装修图纸)**：
  - 它的目标是生成一个**镜像 (Image)**。
  - 它关注的是**环境内部**：装什么驱动、换什么源、默认进哪个目录。
  - 它是静态的，一旦 `build` 完成，镜像里的内容就固化了。
- **docker-compose.yml (运行阶段/物业管理)**：
  - 它的目标是启动**容器 (Container)**。
  - 它关注的是**外部关联**：要把 E 盘哪个目录映射进去、映射哪个端口、连接哪个数据库。
  - 它是动态的，你可以随时停掉、重启或修改映射。

---

在 Dockerfile 中设置 WORKDIR /var/www/html 的作用：

- 执行命令的锚点：如果你后面要在 Dockerfile 里运行 RUN composer install，它会自动在 /var/www/html 下执行。
- 默认落脚点：当你执行 docker exec -it my_app_php sh 进去看日志或调代码时，你一进去就直接在 /var/www/html 目录，不需要再手动 cd 好几层。
- 确定运行环境：它告诉 PHP-FPM 进程，默认的工作路径就在这里。



一个形象的类比

| **概念**             | **对应你的 PHP 开发**       | **类比**                                                     |
| -------------------- | --------------------------- | ------------------------------------------------------------ |
| **Dockerfile**       | `composer.json` + `php.ini` | **房子的施工图纸**。规定了地基、水管（扩展）、层高（WORKDIR）。 |
| **镜像 (Image)**     | 已经打包好的 `.zip` 源码    | **样板房**。看得到摸得着，但你不能住在里面。                 |
| **.yml 文件**        | `Nginx.conf` + 域名绑定     | **房产证/租赁合同**。规定了样板房放在 E 盘（映射），大门朝向 8080 端口。 |
| **容器 (Container)** | 正在跑的进程                | **你住进去后的房子**。这就是实实在在运行着的 PHP 进程。      |



> docker exec my_app_php php -m | grep redis
docker compose up -d --build
