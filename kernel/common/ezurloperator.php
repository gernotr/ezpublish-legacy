<?php
//
// Definition of eZURLOperator class
//
// Created on: <18-Apr-2002 12:15:07 amos>
//
// Copyright (C) 1999-2003 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/home/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//


/*!
 \class eZURLOperator ezurloperator.php
 \brief Collection of url modifying operators

*/

define( 'EZ_HTTP_OPERATOR_TYPE_POST', 1 );
define( 'EZ_HTTP_OPERATOR_TYPE_GET', 2 );
define( 'EZ_HTTP_OPERATOR_TYPE_SESSION', 3 );

class eZURLOperator
{
    /*!
     Initializes the image operator with the operator name $name.
    */
    function eZURLOperator( $url_name = 'ezurl',
                            $urlroot_name = 'ezroot',
                            $ezsys_name = 'ezsys',
                            $design_name = 'ezdesign',
                            $image_name = 'ezimage',
                            $ext_name = 'exturl',
                            $httpName = 'ezhttp',
                            $iniName = 'ezini' )
    {
        $this->Operators = array( $url_name, $urlroot_name, $ezsys_name, $design_name, $image_name, $ext_name, $httpName, $iniName );
        $this->URLName = $url_name;
        $this->URLRootName = $urlroot_name;
        $this->SysName = $ezsys_name;
        $this->DesignName = $design_name;
        $this->ImageName = $image_name;
        $this->ExtName = $ext_name;
        $this->HTTPName = $httpName;
        $this->ININame=  $iniName;
        $this->Sys =& eZSys::instance();
    }

    /*!
     Returns the operators in this class.
    */
    function &operatorList()
    {
        return $this->Operators;
    }

    function namedParameterList()
    {
        return array( 'quote_val' => array( 'type' => 'string',
                                            'required' => false,
                                            'default' => 'double' ) );
    }

    /*!
     */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case $this->ININame:
            {
                if ( count( $operatorParameters ) > 0 )
                {
                    $iniGroup = $tpl->elementValue( $operatorParameters[0], $rootNamespace, $currentNamespace );
                    if ( count( $operatorParameters ) > 1 )
                    {
                        $iniVariable = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );
                        $iniName = false;
                        if ( count( $operatorParameters ) > 2 )
                        {
                            $iniName = $tpl->elementValue( $operatorParameters[2], $rootNamespace, $currentNamespace );
                        }
                        include_once( 'lib/ezutils/classes/ezini.php' );
                        if ( $iniName !== false )
                            $ini =& eZINI::instance( $iniName );
                        else
                            $ini =& eZINI::instance();
                        if ( $ini->hasVariable( $iniGroup, $iniVariable ) )
                            $operatorValue = $ini->variable( $iniGroup, $iniVariable );
                        else
                        {
                            if ( $iniName === false )
                                $iniName = 'site.ini';
                            $tpl->error( $operatorValue, "No such variable '$iniVariable' in group '$iniGroup' for $iniName" );
                        }
                    }
                    else
                        $tpl->error( $operatorName, "Missing variable name parameter" );
                }
                else
                    $tpl->error( $operatorName, "Missing group name parameter" );
            } break;

            case $this->HTTPName:
            {
                include_once( 'lib/ezutils/classes/ezhttptool.php' );
                $http =& eZHTTPTool::instance();
                if ( count( $operatorParameters ) > 0 )
                {
                    $httpType = EZ_HTTP_OPERATOR_TYPE_POST;
                    $httpName = $tpl->elementValue( $operatorParameters[0], $rootNamespace, $currentNamespace );
                    if ( count( $operatorParameters ) > 1 )
                    {
                        $httpTypeName = strtolower( $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace ) );
                        if ( $httpTypeName == 'post' )
                            $httpType = EZ_HTTP_OPERATOR_TYPE_POST;
                        else if ( $httpTypeName == 'get' )
                            $httpType = EZ_HTTP_OPERATOR_TYPE_GET;
                        else if ( $httpTypeName == 'session' )
                            $httpType = EZ_HTTP_OPERATOR_TYPE_SESSION;
                        else
                            $tpl->warning( $operatorName, "Unknown http type '$httpTypeName'" );
                    }
                    switch( $httpType )
                    {
                        case EZ_HTTP_OPERATOR_TYPE_POST:
                        {
                            if ( $http->hasPostVariable( $httpName ) )
                                $operatorValue = $http->postVariable( $httpName );
                            else
                                $tpl->error( $operatorName, "Unknown post variable '$httpName'" );
                        } break;
                        case EZ_HTTP_OPERATOR_TYPE_GET:
                        {
                            if ( $http->hasGetVariable( $httpName ) )
                                $operatorValue = $http->getVariable( $httpName );
                            else
                                $tpl->error( $operatorName, "Unknown get variable '$httpName'" );
                        } break;
                        case EZ_HTTP_OPERATOR_TYPE_SESSION:
                        {
                            if ( $http->hasSessionVariable( $httpName ) )
                                $operatorValue = $http->sessionVariable( $httpName );
                            else
                                $tpl->error( $operatorName, "Unknown session variable '$httpName'" );
                        } break;
                    }
                }
                else
                {
                    $operatorValue = $http;
                }
                return;
            } break;

            case $this->URLName:
            {
                if ( preg_match( "#^[a-zA-Z0-9]+:#", $operatorValue ) or
                     substr( $operatorValue, 0, 2 ) == '//' )
                     return;
                if ( $operatorValue[0] != '/' )
                    $operatorValue = '/' . $operatorValue;
                $operatorValue = $this->Sys->indexDir() . $operatorValue;
                $operatorValue = preg_replace( "#^(//)#", "/", $operatorValue );

                if ( $operatorValue == "" )
                    $operatorValue = "/";
            } break;

            case $this->URLRootName:
            {
                if ( preg_match( "#^[a-zA-Z0-9]+:#", $operatorValue ) or
                     substr( $operatorValue, 0, 2 ) == '//' )
                     return;
                if ( strlen( $operatorValue ) > 0 and
                     $operatorValue[0] != '/' )
                    $operatorValue = '/' . $operatorValue;
                $operatorValue = $this->Sys->wwwDir() . $operatorValue;
            } break;

            case $this->SysName:
            {
                if ( count( $operatorParameters ) == 0 )
                    $tpl->warning( 'eZURLOperator' . $operatorName, 'Requires attributename' );
                else
                {
                    $sysAttribute = $tpl->elementValue( $operatorParameters[0], $rootNamespace, $currentNamespace );
                    if ( !$this->Sys->hasAttribute( $sysAttribute ) )
                        $tpl->warning( 'eZURLOperator' . $operatorName, "No such attribute '$sysAttribute' for eZSys" );
                    else
                        $operatorValue = $this->Sys->attribute( $sysAttribute );
                }
            } break;

            case $this->ImageName:
            {
                $ini =& eZINI::instance();
                $std_base = eZTemplateDesignResource::designSetting( 'standard' );
                $site_base = eZTemplateDesignResource::designSetting( 'site' );
                $std_file = "design/$std_base/images/$operatorValue";
                $site_file = "design/$site_base/images/$operatorValue";
                if ( file_exists( $site_file ) )
                {
                    $operatorValue = $this->Sys->wwwDir() . "/$site_file";
                }
                else if ( file_exists( $std_file ) )
                {
                    $operatorValue = $this->Sys->wwwDir() . "/$std_file";
                }
                else
                    $tpl->warning( $operatorName, "Image '$operatorValue' does not exist in any design" );
            } break;

            case $this->ExtName:
            {
                include_once( 'kernel/classes/datatypes/ezurl/ezurl.php' );
                $urlMD5 = md5( $operatorValue );
                $url =& eZURL::urlByMD5( $urlMD5 );
                if ( $url === false )
                    eZURL::registerURL( $operatorValue );
                else
                    $operatorValue = $url;
            } break;

            case $this->DesignName:
            {
                $ini =& eZINI::instance();
                $std_base = eZTemplateDesignResource::designSetting( 'standard' );
                $site_base = eZTemplateDesignResource::designSetting( 'site' );
                $std_file = "design/$std_base/$operatorValue";
                $site_file = "design/$site_base/$operatorValue";
                if ( file_exists( $site_file ) )
                    $operatorValue = $this->Sys->wwwDir() . "/$site_file";
                else if ( file_exists( $std_file ) )
                    $operatorValue = $this->Sys->wwwDir() . "/$std_file";
                else
                    $tpl->warning( 'eZURLOperator', "Design element $operatorValue does not exist in any design" );
            } break;
        }
        $quote = "\"";
        $val = $namedParameters['quote_val'];
        if ( $val == 'single' )
            $quote = "'";
        else if ( $val == 'no' )
            $quote = false;
        if ( $quote !== false )
            $operatorValue = $quote . $operatorValue . $quote;
    }

    var $Operators;
    var $URLName, $URLRootName, $DesignName, $ImageName;
    var $Sys;
};

?>
