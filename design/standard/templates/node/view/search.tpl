{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{default use_url_translation=false()}
    <td class="{$sequence}">
    <a href={$node.url_alias|ezurl}>{$node.name|wash}</a>
       
    </td>
    <td class="{$sequence}">
      <nobr>{$node.object.class_name|wash}</nobr>
    </td>
{/default}
