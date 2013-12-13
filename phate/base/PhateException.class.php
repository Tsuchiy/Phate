<?php
/**
 * PhateKillException例外
 *
 * 処理を中断する際の例外。exitの代わりにthrowしてください。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateKillException extends Exception
{
}

/**
 * PhateRedirectException例外
 *
 * 他のURLへリダイレクトをする際にthrowする例外。
 * 標準出力は全て破棄されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateRedirectException extends Exception
{
}

/**
 * PhateCommonException例外
 *
 * 汎用例外。エラーとして処理されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateCommonException extends Exception
{
}

/**
 * Phate404Exception例外
 *
 * 実行対象コントローラ等不明例外。処理が中断しエラーとして処理されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class Phate404Exception extends Exception
{
}

/**
 * PhateUnauthorizedException例外
 *
 * 認証失敗例外。リクエストに対する処理を行わずエラーとして処理されます。
 *
 * @package PhateFramework
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateUnauthorizedException extends Exception
{
}
