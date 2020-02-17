# WP Content Framework (Common module)

[![CI Status](https://github.com/wp-content-framework/common/workflows/CI/badge.svg)](https://github.com/wp-content-framework/common/actions)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

[WP Content Framework](https://github.com/wp-content-framework/core) のモジュールです。

## Table of Contents

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
<details>
<summary>Details</summary>

- [要件](#%E8%A6%81%E4%BB%B6)
- [インストール](#%E3%82%A4%E3%83%B3%E3%82%B9%E3%83%88%E3%83%BC%E3%83%AB)
  - [基本設定](#%E5%9F%BA%E6%9C%AC%E8%A8%AD%E5%AE%9A)
- [Author](#author)

</details>
<!-- END doctoc generated TOC please keep comment here to allow auto update -->

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
- [GitHub (Technote)](https://github.com/technote-space)
- [Blog](https://technote.space)
