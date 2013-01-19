
{*
	Template of ListTable
*}

<div {if (0 < strlen($lt_id))} id="{$lt_id}_div" {/if}
	{if (0 < strlen($lt_class))} class="{$lt_class}" {/if}>

	{if (true == $lt_config.pager && true == $lt_config.pager_top)}
	<div id="{$lt_id}_pager_top" class="{$lt_id}_pager">
		{if (!empty($lt_url.p_first))}<a href="{$lt_url.p_first}">
			{$lt_config.pager_text_first}</a>
			{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_prev))}<a href="{$lt_url.p_prev}">
			{$lt_config.pager_text_prev}</a>
			{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_next))}<a href="{$lt_url.p_next}">
			{$lt_config.pager_text_next}</a>
			{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_last))}<a href="{$lt_url.p_last}">
			{$lt_config.pager_text_last}</a>
			{$lt_config.pager_text_spacer}{/if}
		{$lt_config.pager_text_cur_value}{$lt_config.pager_text_spacer}
		{$lt_config.pager_text_goto1}
		<form method="get" action="{$lt_url_form}">
			{$lt_url_form_hidden}
			<input type="text" name="{$lt_config.page_param}"
				value="{$lt_config.page_cur|default: 1}"
				size="{if (99 < $lt_config.page_max|default: -1)}<?php
					echo strlen(strval($lt_config.page_max)) - 1;
					?>{else}1{/if}" />
			{$lt_config.pager_text_goto2}
			<input type="submit"
				value="{$lt_config.pager_text_goto3|default: '转'}" />
		</form>
	</div>
	{/if}

	<table>
		{if (!empty($lt_title))}
		<thead>
		{* tr of th cannot add str, use th instead. *}
		<tr>
		{foreach from=$lt_title key=k_th item=title}
			<th {$lt_config.th_add[$k_th]|default: ''}>
				{if 1==$lt_config.orderby}
					<a href="{if $k_th==$lt_config.orderby_idx}{$lt_url.o_cur}{else}{$lt_url.o_other}{/if}&{$lt_config.orderby_param}_idx={$k_th}">
						{$title}{$k_th}
						{if $k_th==$lt_config.orderby_idx}
							{$lt_config.orderby_text}
						{/if}
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
		{foreach from=$lt_data item=row key=k_tr}
		<tr {$lt_config.tr_add[$k_tr]|default: ''}>
			{foreach from=$row item=col key=k_td}
			<td {$lt_config.td_add[$k_td]|default: ''}>
				{$col}
			</td>
			{/foreach}
		</tr>
		{/foreach}
		</tbody>
	</table>

	{if (true == $lt_config.pager && true == $lt_config.pager_bottom)}
	<div id="{$lt_id}_pager_bottom" class="{$lt_id}_pager">
	{* Same with upper pager text *}
		{if (!empty($lt_url.p_first))}<a href="{$lt_url.p_first}">
			{$lt_config.pager_text_first}</a>
			{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_prev))}<a href="{$lt_url.p_prev}">
			{$lt_config.pager_text_prev}</a>
			{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_next))}<a href="{$lt_url.p_next}">
			{$lt_config.pager_text_next}</a>
			{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_last))}<a href="{$lt_url.p_last}">
			{$lt_config.pager_text_last}</a>
			{$lt_config.pager_text_spacer}{/if}
		{$lt_config.pager_text_cur_value}{$lt_config.pager_text_spacer}
		{$lt_config.pager_text_goto1}
		<form method="get" action="{$lt_url_form}">
			{$lt_url_form_hidden}
			<input type="text" name="{$lt_config.page_param}"
				value="{$lt_config.page_cur|default: 1}"
				size="{if (99 < $lt_config.page_max|default: -1)}<?php
					echo strlen(strval($lt_config.page_max)) - 1;
					?>{else}1{/if}" />
			{$lt_config.pager_text_goto2}
			<input type="submit"
				value="{$lt_config.pager_text_goto3|default: '转'}" />
		</form>
	</div>
	{/if}

</div>


{*
	Coloring rows
	Using Id because when having multi-list, their id is different.
*}

{if (0 < strlen($lt_id))}
<script type="text/javascript">
<!--//--><![CDATA[//>
<!--
	/* 把变色部分的 style 写入 head，直接在 body 中写不符合规范 */
	$("head").append("\
		<style type=\"text/css\" media=\"screen, print\">\
		<!--\
		/* th 用 class 不起作用，改成直接对 styles 属性赋值 2/2 */\
		/*\
		.{$lt_id}_th {literal}{{/literal}\
			background-color: {$lt_config.color_bg_th};\
		{literal}}{/literal}\
		*/\
		.{$lt_id}_tr_even {literal}{{/literal}\
			background-color: {$lt_config.color_bg_tr_even};\
		{literal}}{/literal}\
		.{$lt_id}_tr_odd {literal}{{/literal}\
			background-color: {$lt_config.color_bg_tr_odd};\
		{literal}}{/literal}\
		/* 这个必须写在even/odd后面，不然不生效 */\
		.{$lt_id}_tr_hover {literal}{{/literal}\
			background-color: {$lt_config.color_bg_tr_hover};\
		{literal}}{/literal}\
		-->\
		</style>\
	");

/*
	// 旧的未使用 class 设置属性的方法
	// 现在用 class 实现，能够更好的处理鼠标移入和移出的变色
	$("#{$lt_id} td").css("background-color", "{$lt_config.color_bg_th}");
	$("#{$lt_id} tr:even").css("background-color", "{$lt_config.color_bg_tr_even}");
	$("#{$lt_id} tr:odd").css("background-color", "{$lt_config.color_bg_tr_odd}");
*/
	/* 设置行颜色、隔行变色 */
	/* th 用 class 不起作用，改成直接对 styles 属性赋值 1/2 */
	/* $("#{$lt_id}_div th").addClass(".{$lt_id}_th"); */
	$("#{$lt_id}_div th").css("background-color", "{$lt_config.color_bg_th}");
	$("#{$lt_id}_div tbody tr:even").addClass("{$lt_id}_tr_even");
	/* $("#{$lt_id}_div tbody tr:hover").addClass("{$lt_id}_tr_hover"); */
	$("#{$lt_id}_div tbody tr:odd").addClass("{$lt_id}_tr_odd");
	/* When mouseover and mouseout, change color */
	$("#{$lt_id}_div tbody tr").mouseover(function() {literal}{{/literal}
		$(this).addClass("{$lt_id}_tr_hover");
		{literal}}{/literal});
	$("#{$lt_id}_div tbody tr").mouseout(function() {literal}{{/literal}
		$(this).removeClass("{$lt_id}_tr_hover");
		{literal}}{/literal});

	/* Vertical align of td */
	$("#{$lt_id}_div th").css("vertical-align", "middle");
	$("#{$lt_id}_div td").css("vertical-align", "middle");

	/* Pager\'s width is same with table, and position */
	$(".{$lt_id}_pager").css("display", "block");
	$(".{$lt_id}_pager").css("text-align", "right");
	/* Pager top leave a little margin-bottom to look better */
	$("#{$lt_id}_pager_top").css("margin-bottom", "0.1em");
	if (!$.support.boxModel)
	{literal}{{/literal}
		$(".{$lt_id}_pager").css("width", $("#{$lt_id}_div table").attr("clientWidth"));
		/* Same left margin with table */
		$(".{$lt_id}_pager").css("margin-left"
			, (0 < $("#{$lt_id}_div").attr("clientWidth"))
				? (($("#{$lt_id}_div").attr("clientWidth")
					- $("#{$lt_id}_div table").attr("clientWidth")) / 2)
				: $("#{$lt_id}_div table").attr("margin-left")
			);
	{literal}}{/literal}
	else
	{literal}{{/literal}
		$(".{$lt_id}_pager").css("width"
			, $("#{$lt_id}_div table").css("width").replace("px", "") * 1);
		$(".{$lt_id}_pager").css("margin-left"
			, ($("#{$lt_id}_div").css("width").replace("px", "") * 1
			- $("#{$lt_id}_div table").css("width").replace("px", "") * 1) / 2);
	{literal}}{/literal}

	/* Form vision */
	$(".{$lt_id}_pager form").css("display", "inline");
	/* Pager input auto select when click */
	$(".{$lt_id}_pager form input").mouseover(function() {literal}{{/literal}
		this.select();
		{literal}}{/literal});

//--><!]]>
</script>
{/if}
