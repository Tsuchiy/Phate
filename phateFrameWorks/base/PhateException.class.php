<?php
namespace Phate;
/**
 * KillException例外
 *
 * 処理を中断する際の例外。exitの代わりにthrowしてください。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class KillException extends \Exception
{
}

/**
 * RedirectException例外
 *
 * 他のURLへリダイレクトをする際にthrowする例外。
 * 標準出力は全て破棄されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class RedirectException extends \Exception
{
}

/**
 * CommonException例外
 *
 * 汎用例外。エラーとして処理されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class CommonException extends \Exception
{
}

/**
 * NotFoundException例外
 *
 * 実行対象コントローラ等不明例外。処理が中断しエラーとして処理されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class NotFoundException extends \Exception
{
}

/**
 * UnauthorizedException例外
 *
 * 認証失敗例外。リクエストに対する処理を行わずエラーとして処理されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class UnauthorizedException extends \Exception
{
}

/**
 * DatabaseException例外
 *
 * データベース関連の例外
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class DatabaseException extends \PDOException
{
}

