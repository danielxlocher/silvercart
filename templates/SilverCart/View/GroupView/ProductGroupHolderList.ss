<% if $Elements %>
<ul class="sc-products mobile clearfix">   
    <% loop $Elements %>
    <li class="clearfix <% if not $Content %>no-content<% end_if %>">
        <a href="{$Link}" title="{$Title}" class="highlight clearfix">
        <% if $groupPicture %>
            <img src="{$groupPicture.Pad(82,82).URL}" alt="{$Title}" class="img-polaroid pull-left" />
        <% end_if %>
            <h2>{$MenuTitle.HTML} <span class="pull-right"><span class="badge">{$ActiveProducts.Count}</span> <span class="icon icon-caret-right"></span></span></h2>
            <% if $Content %><small>{$Content.NoHTML.LimitWordCount}</small><% end_if %>
        </a>
    </li>
    <% end_loop %>
</ul>
<div class="ProductGroupHolderList">
    <ul class="sc-products clearfix">   
        <% loop $Elements %>
        <li class="clearfix row-fluid">
            <div class="span3">
                <div class="thumbnail">
                <% if $groupPicture %>
                    <a href="{$Link}" title="{$Title.HTML}">{$groupPicture.Pad(210,140)}</a>
                    <% end_if %>
                </div>
            </div>       
            <div class="span6">
                <h2><a href="{$Link}" class="highlight">{$Title.HTML}</a></h2>
                <p>{$Content.NoHTML.LimitWordCount}</p>
            </div>
            <div class="span3">
            <% if $hasProductCount(1) %>
                <a href="{$Link}" class="btn btn-primary btn-small" title="<%t SilverCart\Model\Pages\ProductGroupHolder.SHOW_PRODUCTS_WITH_COUNT_SINGULAR 'Show {count} product' count=$ActiveProducts.Count %>"><strong><%t SilverCart\Model\Pages\ProductGroupHolder.SHOW_PRODUCTS_WITH_COUNT_SINGULAR 'Show {count} product' count=$ActiveProducts.Count %> <span class="icon-caret-right"></span></a>
            <% else_if not $hasProductCount(0) %>
                <a href="{$Link}" class="btn btn-primary btn-small" title="<%t SilverCart\Model\Pages\ProductGroupHolder.SHOW_PRODUCTS_WITH_COUNT_PLURAL 'Show {count} product' count=$ActiveProducts.Count %>"><strong><%t SilverCart\Model\Pages\ProductGroupHolder.SHOW_PRODUCTS_WITH_COUNT_PLURAL 'Show {count} product' count=$ActiveProducts.Count %> </strong><span class="icon-caret-right"></span></a>
            <% end_if %>
            </div>   
        </li>         
        <% end_loop %>
    </ul>
</div> 
<% end_if %>