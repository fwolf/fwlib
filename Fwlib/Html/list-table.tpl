
{*
  Template of Fwlib\Html\ListTable
*}


{* Pager *}

{capture name='pager'}
{if (!empty($listTableUrl.pageFirst))}
    <a href='{$listTableUrl.pageFirst}'>
      {$listTableConfig.pagerTextFirst}</a>{$listTableConfig.pagerTextSpacer}
{/if}
{if (!empty($listTableUrl.pagePrev))}
    <a href='{$listTableUrl.pagePrev}'>
      {$listTableConfig.pagerTextPrev}</a>{$listTableConfig.pagerTextSpacer}
{/if}
{if (!empty($listTableUrl.pageNext))}
    <a href='{$listTableUrl.pageNext}'>
      {$listTableConfig.pagerTextNext}</a>{$listTableConfig.pagerTextSpacer}
{/if}
{if (!empty($listTableUrl.pageLast))}
    <a href='{$listTableUrl.pageLast}'>
      {$listTableConfig.pagerTextLast}</a>{$listTableConfig.pagerTextSpacer}
{/if}
    {$listTableInfo.pagerTextBody}{$listTableConfig.pagerTextSpacer}
    {$listTableConfig.pagerTextJump1}
    <form method='get' action='{$listTableUrl.form}'>
      {$listTablePagerHidden|default: ''}
      <input type='text' name='{$listTableConfig.paramPage}'
        value='{$listTableInfo.page|default: 1}'
        size='{if (99 < $listTableInfo.pageMax|default: -1)
          }{strlen($listTableInfo.pageMax) - 1}{else}1{/if}' />
      {$listTableConfig.pagerTextJump2}
      <input type='submit'
        value='{$listTableConfig.pagerTextJumpButton|default: '转'}' />
    </form>
{/capture}


{* Main block *}

<div{if (0 < strlen($listTableInfo.id))} id='{$listTableInfo.id}'{/if}
{if (0 < strlen($listTableInfo.class))} class='{$listTableInfo.class}'{/if}>

{if ($listTableConfig.pagerAbove)}
  <div id='{$listTableInfo.idPrefix}pager--above'
    class='{$listTableInfo.classPrefix}pager'>
{$smarty.capture.pager}
  </div>
{/if}

  <table>
{if (!empty($listTableTitle))}
    <thead>
    {* tr of th cannot add str, use th instead. *}
    <tr>
  {foreach $listTableTitle as $keyTh => $title}
      <th {$listTableConfig.thAdd[$keyTh]|default: ''}>
    {if (!empty($listTableInfo.orderByColumn) &&
        isset($listTableInfo.orderByColumn[$keyTh]))}
        <a href='{strip}
      {if $keyTh==$listTableConfig.orderBy}
          {$listTableUrl.obCur}
      {else}
          {$listTableUrl.obOther}
      {/if}
          &amp;{$listTableConfig.paramOrderby}={$keyTh}
        {/strip}'>
          {$title}
      {if $keyTh==$listTableConfig.orderBy}{$listTableConfig.orderByText}{/if}
        </a>
    {else}
      {$title}
    {/if}
      </th>
  {/foreach}
    </tr>
    </thead>
{/if}

    <tbody>
{foreach $listTableData as $keyTr => $row}
      <tr {$listTableConfig.trAdd[$keyTr]|default: ''}>
  {* Display data by title order *}
  {foreach $listTableTitle as $keyTd => $title}
        <td {$listTableConfig.tdAdd[$keyTd]|default: ''}>
          {$row[$keyTd]}
        </td>
  {/foreach}
      </tr>
{/foreach}
    </tbody>
  </table>

{if ($listTableConfig.pagerBelow)}
  <div id='{$listTableInfo.idPrefix}pager--below'
    class='{$listTableInfo.classPrefix}pager'>
{$smarty.capture.pager}
  </div>
{/if}

</div>


{*
  Coloring rows
  Using Id because when having multi-list, their id is different.
*}

{if (0 < strlen($listTableInfo.classPrefix))}
<script type='text/javascript'>
<!--//--><![CDATA[//>
<!--
(function () {
  /* Write class for coloring rows to header */
  $('head').append('\
    <style type=\'text/css\' media=\'screen, print\'>\
    <!--\
    /* th 用 class 不起作用，改成直接对 styles 属性赋值 2/2 */\
    /*\
    .{$listTableInfo.classPrefix}th { \
      background-color: {$listTableConfig.colorBgTh};\
    } \
    */\
    .{$listTableInfo.classPrefix}tr--even { \
      background-color: {$listTableConfig.colorBgTrEven};\
    } \
    .{$listTableInfo.classPrefix}tr--odd { \
      background-color: {$listTableConfig.colorBgTrOdd};\
    } \
    /* hover define must after even/odd */ \
    .{$listTableInfo.classPrefix}tr--hover { \
      background-color: {$listTableConfig.colorBgTrHover};\
    } \
    -->\
    </style>\
  ');

  var listTable = $('#{$listTableInfo.id}');

  /* Set color of tr */
  /* th 用 class 不起作用，改成直接对 styles 属性赋值 1/2 */
  /* $('th', listTable).addClass('.{$listTableInfo.classPrefix}th'); */
  $('th', listTable).css('background-color', '{$listTableConfig.colorBgTh}');
  $('tbody tr:even', listTable).addClass('{$listTableInfo.classPrefix}tr--even');
  $('tbody tr:odd', listTable).addClass('{$listTableInfo.classPrefix}tr--odd');
  /* $('tbody tr:hover', listTable).addClass('{$listTableInfo.classPrefix}tr--hover'); */

  /* Set color change for hover, when mouseover and mouseout. */
  $('tbody tr', listTable).mouseover(function() {
    $(this).addClass('{$listTableInfo.classPrefix}tr--hover');
  });
  $('tbody tr', listTable).mouseout(function() {
    $(this).removeClass('{$listTableInfo.classPrefix}tr--hover');
  });

  /* Vertical align of td */
  $('th', listTable).css('vertical-align', 'middle');
  $('td', listTable).css('vertical-align', 'middle');

  /* Pager width is same with listTable, and position */
  var pager = $('#{$listTableInfo.idPrefix}pager--above, #{$listTableInfo.idPrefix}pager--below');
  pager.css('display', 'block');
  pager.css('text-align', 'right');
  /* Pager above leave a little margin-bottom to look better */
  pager.css('margin-bottom', '0.1em');
  if (!$.support.boxModel) {
    pager.css('width', $('table', listTable).attr('clientWidth'));
    /* Same left margin with listTable */
    pager.css(
      'margin-left',
      (0 < listTable.attr('clientWidth'))
      ? ((listTable.attr('clientWidth') - $('table', listTable).attr('clientWidth')) / 2)
      : $('table', listTable).attr('margin-left')
    );
  } else {
    pager.css('width', $('table', listTable).css('width').replace('px', '') * 1);
    pager.css(
      'margin-left',
      (listTable.css('width').replace('px', '') * 1
      - $('table', listTable).css('width').replace('px', '') * 1) / 2
    );
  }

  /* Form vision */
  $('.{$listTableInfo.classPrefix}pager form').css('display', 'inline');
  /* Pager input auto select when click */
  $('.{$listTableInfo.classPrefix}pager form input').mouseover(function() {
    this.select();
  });
}) ();

//--><!]]>
</script>
{/if}
