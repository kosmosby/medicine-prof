<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\FieldTable;
use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class cbautoactionsActionField extends cbPluginHandler
{

	/**
	 * @param cbautoactionsActionTable $trigger
	 * @param UserTable $user
	 */
	public function execute( $trigger, $user )
	{
		global $_CB_database;

		if ( ! $user->get( 'id' ) ) {
			if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
				var_dump( CBTxt::T( 'AUTO_ACTION_FIELD_NO_USER', ':: Action [action] :: Field skipped due to no user', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
			}

			return;
		}

		foreach ( $trigger->getParams()->subTree( 'field' ) as $row ) {
			/** @var ParamsInterface $row */
			$fieldId				=	$row->get( 'field', null, GetterInterface::INT );

			if ( ! $fieldId ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_FIELD_NO_FIELD', ':: Action [action] :: Field skipped due to missing field', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
				}

				continue;
			}

			/** @var FieldTable[] $fields */
			static $fields			=	array();

			if ( ! isset( $fields[$fieldId] ) ) {
				$field				=	new FieldTable();

				$field->load( (int) $fieldId );

				$fields[$fieldId]	=	$field;
			}

			if ( ! $fields[$fieldId] ) {
				if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
					var_dump( CBTxt::T( 'AUTO_ACTION_FIELD_DOES_NOT_EXIST', ':: Action [action] :: Field skipped due to field [field_id] does not exist', array( '[action]' => (int) $trigger->get( 'id' ), '[field_id]' => (int) $fieldId ) ) );
				}

				continue;
			}

			$operator				=	$row->get( 'operator', 'set', GetterInterface::STRING );
			$value					=	$trigger->getSubstituteString( $row->get( 'value', null, GetterInterface::RAW ), false, $row->get( 'translate', false, GetterInterface::BOOLEAN ) );
			$fieldName				=	$fields[$fieldId]->get( 'name' );
			$fieldColumn			=	$_CB_database->NameQuote( $fieldName );

			if ( ( ! in_array( $fields[$fieldId]->get( 'type' ), array( 'integer', 'counter' ) ) ) && in_array( $operator, array( 'add', 'subtract', 'divide', 'multiply' ) ) ) {
				$operator			=	'set';
			}

			switch ( $operator ) {
				case 'prefix':
					$fieldValue		=	( $value . $user->get( $fieldName ) );
					break;
				case 'suffix':
					$fieldValue		=	( $user->get( $fieldName ) . $value );
					break;
				case 'add':
					$fieldValue		=	( (int) $user->get( $fieldName ) + (int) $value );
					break;
				case 'subtract':
					$fieldValue		=	( (int) $user->get( $fieldName ) - (int) $value );
					break;
				case 'divide':
					$fieldValue		=	( (int) $user->get( $fieldName ) / (int) $value );
					break;
				case 'multiply':
					$fieldValue		=	( (int) $user->get( $fieldName ) * (int) $value );
					break;
				case 'set':
				default:
					$fieldValue		=	$value;
					break;
			}

			$query					=	'UPDATE ' . $_CB_database->NameQuote( $fields[$fieldId]->get( 'table' ) )
									.	"\n SET " . $fieldColumn . " = " . $_CB_database->Quote( $fieldValue )
									.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $user->get( 'id' );
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			$user->set( $fieldName, $fieldValue );
		}
	}
}