<div class="modal-header">
  <h6 class="modal-title">
    {include file='__svg_icons.tpl' icon="products" class="main-icon mr10" width="24px" height="24px"}
    {__("Edit Product")}
  </h6>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form class="js_ajax-forms" data-url="posts/edit.php">
  <div class="modal-body">
    <div class="form-group">
      <label class="form-label">{__("Product Name")}</label>
      <input name="name" type="text" class="form-control" value="{$post['product']['name']}">
    </div>
    <div class="row">
      <div class="form-group col-md-8">
        <label class="form-label">{__("Total Item Units")}</label>
        <input name="quantity" type="number" min="1" value="{$post['product']['quantity']}" class="form-control">
      </div>
      <div class="form-group col-md-4">
        <label class="form-label">{__("Price")} ({$system['system_currency']})</label>
        <input name="price" type="text" class="form-control" value="{$post['product']['price']}">
      </div>
    </div>
    <div class="row">
      <div class="form-group col-md-8">
        <label class="form-label">{__("Category")}</label>
        <select class="form-select" name="category">
          {foreach $market_categories as $category}
            {include file='__categories.recursive_options.tpl' data_category=$post['product']['category_id']}
          {/foreach}
        </select>
      </div>
      <div class="form-group col-md-4">
        <label class="form-label">{__("Status")}</label>
        <select class="form-select" name="status">
          <option {if $post['product']['status'] == "new"}selected{/if} value="new">{__("New")}</option>
          <option {if $post['product']['status'] == "old"}selected{/if} value="old">{__("Used")}</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">{__("Location")}</label>
      <input name="location" type="text" class="form-control js_geocomplete" value="{$post['product']['location']}">
    </div>
    <div class="form-group">
      <label class="form-label">{__("Description")}</label>
      <textarea name="message" rows="5" dir="auto" class="form-control">{$post['text_plain']}</textarea>
    </div>
    <!-- custom fields -->
    {if $custom_fields['basic']}
      {include file='__custom_fields.tpl' _custom_fields=$custom_fields['basic'] _registration=false}
    {/if}
    <!-- custom fields -->
    <!-- error -->
    <div class="alert alert-danger mt15 mb0 x-hidden"></div>
    <!-- error -->
  </div>
  <div class="modal-footer">
    <input type="hidden" name="handle" value="product">
    <input type="hidden" name="id" value="{$post['post_id']}">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{__("Cancel")}</button>
    <button type="submit" class="btn btn-primary">{__("Save")}</button>
  </div>
</form>