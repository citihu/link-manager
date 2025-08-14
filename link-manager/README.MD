# Link Manager

专注于管理友情链接的 WordPress 插件。

## 🌁 效果
[友情链接](https://woolyun.com/friends-links/)
![后台图1](https://github.com/citihu/link-manager/blob/main/link-manager/%E5%90%8E%E5%8F%B0%E5%9B%BE1.JPG)
![后台图2](https://github.com/citihu/link-manager/blob/main/link-manager/%E5%90%8E%E5%8F%B0%E5%9B%BE2.JPG)
![效果图](https://github.com/citihu/link-manager/blob/main/link-manager/%E6%95%88%E6%9E%9C%E5%9B%BE.JPG)

## ✨ 特色功能

- **后台友链管理** ：提供添加、编辑、删除、批量删除、拖拽排序、导入导出 CSV 等功能。
- **前端展示** ：通过 [link_manager] 短代码展示友情链接，采用响应式网格布局。
- **数据存储** ：创建专用数据库表存储友情链接数据。
- **安全性** ：使用 nonce 保护表单，对所有输入输出进行消毒和验证。
- **国际化** ：提供 POT 文件，支持多语言展示各个文件具体功能

## 📦 安装方法

1. 下载zip文件，到 `WordPress 后台 - 插件 - 添加插件`
2. 启用插件后，在左侧菜单找到「友情链接管理」开始使用

## 🚀 使用方法

### 管理友链

1. 进入 WordPress 后台 → 友情链接管理
2. 点击「添加新链接」添加友链
3. 填写必要信息：
   - 站点名称
   - 站点地址
   - 站点头像URL（可选）
   - 站点头像（上传图片）（可选）
   - 站点描述（可选）

### 显示友链

在页面或文章中使用短代码：

```
[link_manager]
```
## ⚙️ 系统要求

- WordPress 6.8.2+
- PHP 8.4.6+

## 📄 许可证

GPL-2.0+

## 👨 作者

天海牧歌 - [https://woolyun.com](https://woolyun.com)

## 文件说明
### 文件功能
1. link-manager.php ：主插件文件，负责定义常量、自动加载类文件、初始化插件和注册卸载钩子。
2. class-linkmanager-plugin.php ：主类，负责插件的初始化、加载文本域、创建数据库表和卸载清理操作。
3. class-linkmanager-table.php ：继承 WP_List_Table 类，用于在后台展示友情链接列表。
4. class-linkmanager-shortcode.php ：处理 [link_manager] 短代码，负责前端展示和加载前端资源。
5. class-linkmanager-admin.php ：负责友情链接后台管理界面的创建、处理各种 AJAX 请求。
6. admin.css ：后台管理界面的样式文件。
7. frontend.css ：前端展示界面的样式文件。
8. admin.js ：后台管理界面的 JavaScript 文件，处理表单提交、删除、排序、导入导出等交互逻辑。
9. frontend.js ：前端展示界面的 JavaScript 文件，可用于添加前端交互逻辑。
10. link-manager.pot ：国际化 POT 文件，用于多语言翻译。


