# WP Content Framework (Common module)

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

[WP Content Framework](https://github.com/wp-content-framework/core) のモジュールです。

# 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

# インストール

``` composer require wp-content-framework/common ```  

## 基本設定
- configs/config.php  

|設定値|説明|
|---|---|
|cache_filter_result|filterをキャッシュするかどうかを設定 \[default = true]|
|cache_filter_exclude_list|キャッシュから除外するfilterを設定 \[default = []]|

- configs/settings.php

|設定値|説明|
|---|---|
|use_filesystem_credentials|ファイルシステム認証を使用するかどうかを設定 \[default = false]<br>KUSANAGI等のファイルシステム認証が必要な環境で有効にすると正しく動作するようになる場合があります|

# Author

[GitHub (Technote)](https://github.com/technote-space)  
[Blog](https://technote.space)
