# 非同期Http通信サーバサイド PHPフレームワーク "Phate"

## "Phate" as "PHP http application thin engine"

最近のMVCフレームワークの傾向とその重量を意識した上で、  
かなり機能を省いた軽量Webフレームワークを作成し採用しました。  
まだ機能的に足りない部分もありますので、  
デバッグ・更新のメンテナンスをする場面もあると思いますがよろしくお願いします。  

## ディレクトリ構成


    -- [root] -+  
               + [cache] (キャッシュ保存ディレクトリ、権限を0777に設定しておいてください。deployの際は中身を掃いてください）  
               + [config] (設定ファイルディレクトリ)  
               + [phate] + (フレームワークディレクトリ)  
               |          + [base] (フレームワーク)  
               |          |   + [lib] (フレームワークライブラリ)  
               |          + [renderers] (レンダラー)  
               |          + [scaffold] (scaffold実行スクリプト)  
               |          + [vendor] (他の提供するライブラリ)  
               |  
               + [htdocs] + (httpdのドキュメントルート用ディレクトリ)   
               |          + [(プロジェクト名)] (プロジェクトのドキュメントルート)  
               |  
               + [logs] (ログ保存用ディレクトリ、使用は任意。権限をhttpユーザ、バッチユーザ、フレームワークユーザが書き込める様に設定しておいてください)  
               + [project] + (各プロジェクトのコードを配置するディレクトリ)  
               |           + [(プロジェクト名)] (プロジェクトのソースルート)  
               |               + [batch] （バッチプログラム）  
               |               + [controllers] (コントローラ)  
               |               + [data] (プログラム実行時のデータやキャッシュを置くディレクトリ、権限を0775に設定しておいてください)  
               |               |  + [cache]  
               |               |    + [template](テンプレートエンジン使用の際のキャッシュディレクトリ、権限とdeployに要注意)  
               |               + [exception] (例外特別処理)  
               |               + [filters] (フィルタ)  
               |               + [maintenance] (メンテナンス設定用ディレクトリ)  
               |               + [models] (モデル)  
               |               |  + [database] (O/Rマッパ、データベースアクセスモデル)  
               |               + [views] (ビュー、テンプレート)  
               |  
               + [serverEnv] (サーバ環境変数配置用ディレクトリ)  
               + [source] (フレームワーク関連ファイル配置用ディレクトリ)  
               + [test] + (テストコード配置用ディレクトリ)  
               |        + [project] (/root/projectディレクトリに対応)  
               |          + [(プロジェクト名)](コード用テストコード)  
               + scaffold (scaffolding実行ファイル、権限を0755に設定しておいてください)  

  
開発にあたっては"/config/"ディレクトリのyamlファイルの作成・記述と、  
"/htdocs/(プロジェクト名)/"ディレクトリの編集/リソース配置、  
"/project/(プロジェクト名)/"ディレクトリ内へコードの作成が主となります。  

## first step

まずソースを展開し権限の設定をしてください。  
次にrootディレクトリで以下のコマンドを打ちましょう  


    ./scaffold project (任意のプロジェクト名)

これでプロジェクトに関連する基本的なディレクトリとファイルの構築が完了します。</p>

## httpのドキュメントルートの設定

httpのドキュメントルートを"htdocs/(プロジェクト名)"のディレクトリに設定してください。
dispatchにmod_rewriteを利用していますので、AllowOverrideの設定を有効にしてください。

## 実行の流れ

### httpモード

httpアクセスに対しては特定のURIへのリクエストに対して以下の様な実行が行われます


    URI: http://xxxx.xxx.xx/module/controller/?a=b&#38;....  
    　↓  
    (InputFilter)  
    　↓  
    moduleモジュールのcontrollerコントローラの実行 →(例外発生時)ThrownExceptionクラス  
    　↓  
    (OutputFilter)  
    　↓  
    出力  

リクエストの情報はPhateHttpRequestオブジェクトに保存されますし、
PhateHttpResponseHeaderにレスポンスの定義をすると、それに従ったレスポンスが行われます。

### バッチモード
  
バッチプログラムを実行する場合は、バッチ起動用のprojectのbatch内にPHPを作り実行します。  
主にはデータベースやmemcacheなどの設定を共通で使えるという利点のを目的としていますので  
他の定時処理などは通常のバッチ運用で問題ありません。  
（もちろんライブラリを利用する目的でも使えます）  

## 規約

autoloadのためにファイル名は"(クラス名).class.php"で記述してください。  
コーディング規約はpear準拠を推奨しておりますが、特に強制されるものではありません。  

---

# フレームワーク機能

## システム環境  

想定実行環境を参考用に記載しておきます。  

### OS


    CentOS(6.4)

### ライブラリ


    "Japanese Support"   
    "development tools"   
    Apache(2.2)  
    php(5.5)  
    mysql(5.6)  
    memcached  
    libyaml  
    libyaml-devel  
    libmemcached(1.0.16)  
    libmemcached-devel(1.0.16)  
    libcurl  
    libcurl-devel  
    msgpack  
    redis  

### PHP


    php
    php-devel
    php-common
    php-cli
    php-pdo
    php-mysql
    php-mbstring
    php-mcrypt
    php-xml
    php-pear

### pecl


    yaml
    igbinary
    memcached --enable-memcached-igbinary
    zendopcache-beta(PHP5.4以下の場合APCで代用)
    msgpack-beta
    xdebug(profile用)

### pear


    PHPUnit,PHPUnit2(UnitTest用)

### etc


    PHPredis(https://github.com/nicolasff/phpredis)

/phate/lib/vendor以下に


    ・HTML-Emoji(フィーチャフォン向け)(http://libemoji.com/)  
    ・OAuth(gadgetサーバ経由のOAuth通信 https://code.google.com/p/oauth-php/ )  
    ・Twig(レンダラ用)  
    ・Fluent(fluent-logger-php, https://github.com/fluent/fluent-logger-php/tree/master/src)  
    ・AWS SDK(includeすると超重いので要注意)（※次期拡張予定）  

### Database

MySQLを使用します


    http://cdn.mysql.com/Downloads/MySQL-5.6/MySQL-shared-compat-5.6.xx-1.el6.x86_64.rpm
    http://cdn.mysql.com/Downloads/MySQL-5.6/MySQL-shared-5.6.xx-1.el6.x86_64.rpm 
    http://cdn.mysql.com/Downloads/MySQL-5.6/MySQL-server-5.6.xx-1.el6.x86_64.rpm 
    http://cdn.mysql.com/Downloads/MySQL-5.6/MySQL-devel-5.6.xx-1.el6.x86_64.rpm 
    http://cdn.mysql.com/Downloads/MySQL-5.6/MySQL-client-5.6.xx-1.el6.x86_64.rpm


### apache

rewriteModuleを使用できるようにしてください。  
（Allow overrideなど含む）
以下vhost.confの例


    NameVirtualHost *:80
    
    <VirtualHost *:80>
    
        ServerName (ServerName)
    
        DocumentRoot (DocumentRoot)
        <Directory (DocumentRoot)>
            Options FollowSymLinks
            AllowOverride All
    
            Order allow,deny
            Allow from all
            SetEnv (プロジェクト名)_ENV (プロジェクト環境変数)
        </Directory>
    
        LogFormat "%{X-Forwarded-For}i %l %u %t \"%r\" %>s %b %D \"%{Referer}i\" \"%{User-Agent}i\"" local-combined
        LogLevel warn
        ErrorLog logs/app_local_error_log
        CustomLog logs/app_local_access_log local-combined
    </VirtualHost>


### nginx

nginx + php-fpm(FastCgi,下記例ですとwww.sockでsocket通信できるようにしておいてください)時の  
virtual.conf例  


    server {
        listen       80;
        server_name  default;
    
        charset utf-8;
        # access_log  /var/log/nginx/host-kingdom-dev.access.log  main;
    
        # space problem
        if ($request_uri ~ " ") {
          return 444;
        }
    
        location ~ ^/(.*)/(.*)/(.*)$ {
            root           (DocumentRoot);
            fastcgi_pass   unix:/tmp/php-fpm.sock;
            fastcgi_param  SCRIPT_FILENAME  $document_root/index.php;
            include        fastcgi_params;
            fastcgi_param  QUERY_STRING     module=$1&controller=$2&$query_string;
        }
    
        location ~ ^/$ {
            root           (DocumentRoot);
            fastcgi_pass   unix:/tmp/php-fpm.sock;
            fastcgi_param  SCRIPT_FILENAME  $document_root/index.php;
            include        fastcgi_params;
            fastcgi_param  QUERY_STRING     module=index&controller=Index&$query_string;
        }
    
        location ~ \.php$ {
            root           (DocumentRoot);
            fastcgi_pass   unix:/tmp/php-fpm.sock;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
    
        location ~ /\.ht {
            deny  all;
        }
    
        location / {
            root   /(DocumentRoot);
            if (!-e $request_filename){
            }
        }
    
        location = /robots.txt  { access_log off; log_not_found off; }
        location = /favicon.ico { access_log off; log_not_found off; }
    
        #error_page  404              /404.html;
    
        # redirect server error pages to the static page /50x.html
        #
        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   /usr/share/nginx/html;
        }
    
        # healthcheck
        location = /system/health {
          include /usr/local/etc/nginx/conf/fastcgi_params;
          fastcgi_param SCRIPT_FILENAME /system/health;
          fastcgi_pass unix:/tmp/php-fpm.sock;
        }
    
        # php-fpm status
        location = /system/php_fpm_status {
          include /usr/local/etc/nginx/conf/fastcgi_params;
          fastcgi_param SCRIPT_FILENAME /system/php_fpm_status;
          fastcgi_pass unix:/tmp/php-fpm.sock;
          access_log off;
          allow 127.0.0.1;
          deny all;
        }
    
        # nginx status
        location = /system/nginx_status {
          stub_status on;
          access_log off;
          allow 127.0.0.1;
          deny all;
        }
    
    }

---

## プロジェクトコーディング

以下に基本的に作られるディレクトリの利用目的を記載します。
必要であればプロジェクトに応じて自由に拡張してください。

### controller
(モジュール名)のサブディレクトリの下に(コントローラ名).class.phpで作成してください
httpで/(モジュール名)/(コントローラ名)/でリクエストします。

### model
(クラス名).class.phpのファイル名で、プロジェクト内で被りの無い様に作成してください。
（サブディレクトリを切ることも可能です）

### database model
データベースにアクセスするためのモデルです。
scaffoldを利用してO/Rマッパを作成する事も可能ですし、手で作ることも可能ですが、
テーブル単位でのクラスの作成を強く推奨します。

### view
httpのテンプレートなどを置くのに利用してください。

### filter
InputFilter、OutputFilterを配置します。
メンテナンスモード、ユーザIDの設定認証、再送のために用いたり、出力文字コードを変換したりなどに用いることができます。

### exception
例外発生時の処理を記載します

### batch
バッチプログラムを配置できます。
Webと同型のコーディングが行えることと設定ファイルを共有できることが長所となります。

### data
データ配置用のディレクトリです。
アップロードの一時ファイルやtemplateのキャッシュファイルを配置するなど目的に使えます

### test
ディレクトリの階層が若干異なりますが、テスティングフレームワーク機能です。
もちろん他にも適宜必要に応じてディレクトリの作成やautoloadの設定を行えます。

---

## 設定ファイル

記述形式はyamlです。
configディレクトリの下に配置します。

ルートディレクティブがall:の物がデフォルトで読み込まれますが、
サーバの環境変数
(serverの環境変数"(プロジェクト名(大文字))_ENV"が設定されている場合はそちらが優先されます、
設定されていない場合はserverEnv/status.confをcatしてみてください)
のディレクティブの値で上書きされます。

debugモードがfalseの場合、設定ファイルの内容はcache内のファイルに吐き出され次回からこれを読み込みます。
設定ファイルを変更しても反映されない場合は確認してください。


```各ファイルで"%%CONTEXT_ROOT%%"と記載するとcontext rootディレクトリの絶対パスに置き換わります。```

---

## 基本ライブラリ

以下のライブラリが基本のものとして用意されています。


    PhateLogger ... (Logを残したい)
    PhateTimer ... (システム時間を取得したい)
    PhateHttpRequest ... (リクエストに関する情報を取得したい)
    PhateHttpResponseHeader ... (レスポンス情報を制御したい)
    PhateDB(Database) ... (データベースを扱いたい)
    PhateMemcached(libmemcached) ... (memcacheを扱いたい)
    PhateRedis(redis) ... (Redisを扱いたい)
    PhateMbga(モバゲー) ... (mbgaのAPIを使いたい)
    PhateApple(iTunes) ... (Apple関連のサービスを使いたい)
    PhateGoogle ... (google関連のサービスを使いたい)
    PhateFluentd ... (Fluentdに出力したい)


---

## レンダラ

以下のレンダラが基本のものとして用意されています。


    PhatePureRenderer ... (標準出力する)  
    PhateMsgPackRenderer ... (データをmsgpackでシリアライズしバイナリ出力する)  
    PhateTwigRenderer ... (twigテンプレートエンジンを使い出力する)  

---
