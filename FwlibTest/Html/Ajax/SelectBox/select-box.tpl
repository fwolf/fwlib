
<script type='text/javascript'>
<!--//--><![CDATA[//>
<!--
/* Append css define to <head> */
$('head').append('\
<style type="text/css" media="screen, print">\
            <!--\
            #fwlib-sel-box-1-empty, #fwlib-sel-box-1-loading, #fwlib-sel-box-1-row-tpl {\
                display: none;\
            }\
            #fwlib-sel-box-1-empty td, #fwlib-sel-box-1-loading td, #fwlib-sel-box-1-tip td, .fwlib-sel-box-1-col-td-choose {\
                text-align: center;\
            }\
\
                #fwlib-sel-box-1-bg {\
\
            background: #E5E5E5;\
            display: none;\
            filter: alpha(opacity=60);\
            left: 0px;\
            opacity: 0.6;\
            position: absolute;\
            top: 0px;\
            z-index: 998;\
\
                }\
\
                #fwlib-sel-box-1-close-bottom {\
\
            cursor: pointer;\
            margin-top: 0.5em;\
            text-align: right;\
            width: 100%;\
\
                }\
\
                #fwlib-sel-box-1-close-top {\
\
            cursor: pointer;\
            float: right;\
\
                }\
\
                #fwlib-sel-box-1-div {\
\
            background-color: #FFF;\
            border: 1px solid #999;\
            display: none;\
            padding: 0.7em;\
            position: absolute;\
            text-align: center;\
            width: 700px;\
            z-index: 999;\
\
                }\
\
                #fwlib-sel-box-1-table {\
\
            border: 1px solid;\
            border-collapse: collapse;\
            border-spacing: 0;\
            float: none;\
            line-height: 1.2em;\
            text-align: center;\
            vertical-align: baseline;\
            width: 100%;\
\
                }\
\
                #fwlib-sel-box-1-title {\
\
            float: left;\
            font-size: 1.2em;\
            font-weight: bold;\
            margin-bottom: 0.7em;\
            padding-left: 2em;\
            text-align: center;\
            width: 90%;\
\
                }\
\
                #fwlib-sel-box-1-clearit {\
\
            clear: both;\
\
                }\
\
                #fwlib-sel-box-1-empty {\
\
                }\
\
                #fwlib-sel-box-1-loading {\
\
                }\
\
                #fwlib-sel-box-1-tip {\
\
                }\
\
                #fwlib-sel-box-1-row-tpl {\
\
                }\
\
            .fwlib-sel-box-1-row {\
\
            }\
            .fwlib-sel-box-1-tr-hover {\
\
            background-color: #e3e3de;\
\
            }\
\
\
            -->\
            </style>\
\
');
//--><!]]>
</script>
<div id='fwlib-sel-box-1-bg'>
            <iframe style='position: absolute; z-index: -1;'
                frameborder='0' src='about:blank'></iframe>
            </div>
<div id='fwlib-sel-box-1-div'>
            <div id='fwlib-sel-box-1-title'>Select</div>

                <div id='fwlib-sel-box-1-close-top'>Close</div>

                <div id='fwlib-sel-box-1-clearit'></div>

                <label>Title:</label>
                <input type='text' id='fwlib-sel-box-1-query' size='30' />
                <input type='button' id='fwlib-sel-box-1-submit' value='Search' />

                <input type='hidden' id='fwlib-sel-box-1-url' value='' />

            <table id='fwlib-sel-box-1-table'>
                <thead>
                    <tr>
<th>Choose</th>

                    </tr>
                </thead>
                <tbody>
                    <tr id='fwlib-sel-box-1-row-tpl'>
<td class='fwlib-sel-box-1-col-td-choose'>

                            <a href='javascript:void(0);'
                                >Choose</a>
                        </td>
                    </tr>
                    <tr id='fwlib-sel-box-1-loading'>
                        <td colspan='1'>Searching, please stand by ...</td>
                    </tr>
                    <tr id='fwlib-sel-box-1-empty'>
                        <td colspan='1'>No result found, please change search keywords.</td>
                    </tr>
                    <tr id='fwlib-sel-box-1-tip'>
                        <td colspan='1'>Please input sequential part of title to search,
result will only show first 10 items.</td>
                    </tr>
                </tbody>
            </table>

                <div id='fwlib-sel-box-1-close-bottom'>Close</div>
</div>
<script type='text/javascript'>
            <!--//--><![CDATA[//>
            <!--
            /* Set bg height and width */
            $('#fwlib-sel-box-1-bg')
                .css('width', $(document).width())
                .css('height', $(document).height() * 1.2);
            $('#fwlib-sel-box-1-bg iframe')
                .css('width', $(document).width())
                .css('height', $(document).height() * 1.2);

            /* Set click action */
            $('#fwlib-sel-box-1-caller').click(function () {

                $('#fwlib-sel-box-1-bg').show();
                $('#fwlib-sel-box-1-div')
                    .css('top', ((window.innerHeight
                                || document.documentElement.offsetHeight)
                            - $('#fwlib-sel-box-1-div').height())
                        / 3
                        + (document.body.scrollTop
                            || document.documentElement.scrollTop) + 0 + 'px')
                    .css('left', $(window).width() / 2
                        - $('#fwlib-sel-box-1-div').width() / 2
                        + 0 + 'px')
                    .show();

            });

            /* Set query action */
            $('#fwlib-sel-box-1-submit').click(function () {

                if (true) {

                    /* Query begin */
                    $('#fwlib-sel-box-1-tip').hide();
                    $('#fwlib-sel-box-1-loading').show();
                    $('#fwlib-sel-box-1-empty').hide();
                    $.ajax({
                        url: $('#fwlib-sel-box-1-url').val(),
                        data: {'s':
                            $('#fwlib-sel-box-1-query').val()},
                        dataType: 'json',
                        success: function(msg){
                            $('#fwlib-sel-box-1-loading').hide();
                            $('.fwlib-sel-box-1-row').remove();
                            if (0 < msg.length) {
                                /* Got result */
                                $(msg).each(function(){
                                    tr = $('#fwlib-sel-box-1-row-tpl').clone();
                                    tr.addClass('fwlib-sel-box-1-row');

                                    /* Attach onclick event */
                                    /* Cloning in IE will lost event */
                                    $('a', tr).last().click(function () {


                                        $("#fwlib-sel-box-1-div").hide();
                                        $("#fwlib-sel-box-1-bg").hide();
                                    });

                                    /* Row bg-color */
                                    tr.mouseenter(function () {
                                        $(this).addClass('fwlib-sel-box-1-tr-hover');
                                    }).mouseleave(function () {
                                        $(this).removeClass('fwlib-sel-box-1-tr-hover');
                                    });

                                    $('#fwlib-sel-box-1-loading')
                                        .before(tr);

                                    tr.show();
                                });
                            }
                            else {
                                /* No result */
                                $('#fwlib-sel-box-1-empty').show();
                            }
                        }
                    });
                }
                else {
                    /* Nothing to query */
                    $('#fwlib-sel-box-1-tip').show();
                    $('#fwlib-sel-box-1-loading').hide();
                    $('#fwlib-sel-box-1-empty').hide();
                }
            });

                $('#fwlib-sel-box-1-query').keyup(function () {
                    $('#fwlib-sel-box-1-submit').click();
                });

            /* Link to hide select layer */
            $('#fwlib-sel-box-1-close-bottom, #fwlib-sel-box-1-close-top').click(function () {
                $(this).parent().hide();
                $('#fwlib-sel-box-1-bg').hide();
            });
            //--><!]]>
            </script>

