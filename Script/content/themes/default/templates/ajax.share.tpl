<div class="modal-header">
  <h6 class="modal-title">
    {include file='__svg_icons.tpl' icon="share" class="main-icon mr10" width="24px" height="24px"}
    {__("Share")}
  </h6>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">

  <div style="margin: 25px auto;">
    <div class="input-group">
      <input type="text" disabled class="form-control" value="{$share_link}">
      <button type="button" class="btn btn-light js_clipboard" data-clipboard-text="{$share_link}" data-bs-toggle="tooltip" title='{__("Copy")}'>
        <i class="fas fa-copy"></i>
      </button>
    </div>
  </div>

  {if $system['social_share_enabled']}
    <div class="post-social-share border-bottom-0">
      <a href="http://www.facebook.com/sharer.php?u={$share_link}" class="btn btn-sm btn-rounded btn-social-icon btn-facebook" target="_blank">
        <i class="fab fa-facebook-f"></i>
      </a>
      <a href="https://twitter.com/intent/tweet?url={$share_link}" class="btn btn-sm btn-rounded btn-social-icon btn-rounded btn-twitter" target="_blank">
        <i class="fab fa-twitter"></i>
      </a>
      <a href="https://vk.com/share.php?url={$share_link}" class="btn btn-sm btn-rounded btn-social-icon btn-vk" target="_blank">
        <i class="fab fa-vk"></i>
      </a>
      <a href="https://www.linkedin.com/shareArticle?mini=true&url={$share_link}" class="btn btn-sm btn-rounded btn-social-icon btn-linkedin" target="_blank">
        <i class="fab fa-linkedin"></i>
      </a>
      <a href="https://api.whatsapp.com/send?text={$share_link}" class="btn btn-sm btn-rounded btn-social-icon btn-whatsapp d-none d-sm-inline-block" target="_blank">
        <i class="fab fa-whatsapp"></i>
      </a>
      <a href="https://reddit.com/submit?url={$share_link}" class="btn btn-sm btn-rounded btn-social-icon btn-reddit" target="_blank">
        <i class="fab fa-reddit"></i>
      </a>
      <a href="https://pinterest.com/pin/create/button/?url={$share_link}" class="btn btn-sm btn-rounded btn-social-icon btn-pinterest" target="_blank">
        <i class="fab fa-pinterest"></i>
      </a>
    </div>
  {/if}

</div>