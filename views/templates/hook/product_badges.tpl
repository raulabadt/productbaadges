{if isset($badges) && $badges}
  <div class="product-badges">
    {foreach from=$badges item=badge}
      <span class="product-badge badge-{$badge.position|escape:'htmlall':'UTF-8' }" style="background: {$badge.bg_color|escape:'htmlall':'UTF-8'}; color: {$badge.text_color|escape:'htmlall':'UTF-8'};">
        {$badge.text|escape:'htmlall':'UTF-8'}
      </span>
    {/foreach}
  </div>
{/if}
