<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" form="form-uni-catwall" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
			<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
					<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
			<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
			</div>
			<ul class="nav nav-tabs" id="user_log_tabs">
				<li class="active"><a href="#tab-settings" data-toggle="tab"><?php echo $tab_settings; ?></a></li>
				<li><a href="#tab-help" data-toggle="tab"><?php echo $tab_help; ?></a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane" id="tab-help">
					<div class="row">
						<div class="col-md-6">
							<div style="padding:15px;">
								<h3>Назначение модуля :</h3>								
								<p>Модуль предназначен для вывода практически в любом месте (схеме) категорий магазина.</p>
								<h4 class="text-primary">Особенности модуля :</h4>
								<ul>
									<li>Произвольный заголовок модуля</li>
									<li>Возможность совсем убрать вывод заголовка модуля</li>
									<li>Имеет три типа отображения (примеры справа)</li>
									<li>Вывод изображений категорий</li>
									<li>Вывод под-категорий</li>
									<li>Вывод описания категорий</li>
									<li>Разный тип отображения под-категорий (немаркиванный список, маркированный список, в один ряд .Примеры справа.)</li>
									<li>Изменение порядка вывода категори, путём перетаскивания их мышкой в настройках модуля</li>
									<li>Кешированние данных модуля, что позволяет снизить возможную нагрузку модуля на ваш магазин, при выводе большого кол-ва категорий</li>
								</div>
							</div>
							<div class="col-md-6">
								<div style="padding:15px;">
									<h3>Типы отображения модуля и под-категорий :</h3>
									<h4 class="text-primary">Кликните для увеличения</h4>
									<div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist">
										<div class="panel panel-default">
											<div class="panel-heading" id="headingOne" role="tab">
												<h4 class="panel-title"><a aria-controls="collapseOne" aria-expanded="true" data-parent="#accordion" data-toggle="collapse" href="#collapseOne">Тип отображения #1 </a></h4>
											</div>
											<div aria-labelledby="headingOne" class="panel-collapse collapse in" id="collapseOne" role="tabpanel">
												<div class="panel-body">
													<div class="panel-body">
														<a href="view/image/uni_catwall/module_type1.png" data-toggle="lightbox">
															<img src="view/image/uni_catwall/module_type1.png" class="img-fluid" style="width:100%;height:300px">
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="panel panel-default">
											<div class="panel-heading" id="headingTwo" role="tab">
												<h4 class="panel-title"><a aria-controls="collapseTwo" aria-expanded="false" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseTwo">Тип отображения #2 </a></h4>
											</div>
											<div aria-labelledby="headingTwo" class="panel-collapse collapse" id="collapseTwo" role="tabpanel">
												<div class="panel-body">
													<div class="panel-body">
														<a href="view/image/uni_catwall/module_type2.png" data-toggle="lightbox">
															<img src="view/image/uni_catwall/module_type2.png" class="img-fluid" style="width:100%;height:300px">
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="panel panel-default">
											<div class="panel-heading" id="headingThree" role="tab">
												<h4 class="panel-title"><a aria-controls="collapseThree" aria-expanded="false" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseThree">Тип отображения #3 </a></h4>
											</div>
											<div aria-labelledby="headingThree" class="panel-collapse collapse" id="collapseThree" role="tabpanel">
												<div class="panel-body">
													<div class="panel-body">
														<a href="view/image/uni_catwall/module_type3.gif" data-toggle="lightbox">
															<img src="view/image/uni_catwall/module_type3.gif" class="img-fluid" style="width:100%;height:300px">
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="panel panel-default">
											<div class="panel-heading" id="headingFour" role="tab">
												<h4 class="panel-title"><a aria-controls="collapseThree" aria-expanded="false" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseFour">Тип отображения подкатегорий #1 </a></h4>
											</div>
											<div aria-labelledby="headingFour" class="panel-collapse collapse" id="collapseFour" role="tabpanel">
												<div class="panel-body">
													<div class="panel-body">
														<a href="view/image/uni_catwall/subcat_type_mark.png" data-toggle="lightbox">
															<img src="view/image/uni_catwall/subcat_type_mark.png" class="img-fluid" style="width:100%;height:300px">
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="panel panel-default">
											<div class="panel-heading" id="headingFive" role="tab">
												<h4 class="panel-title"><a aria-controls="collapseThree" aria-expanded="false" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseFive">Тип отображения подкатегорий #2 </a></h4>
											</div>
											<div aria-labelledby="headingFive" class="panel-collapse collapse" id="collapseFive" role="tabpanel">
												<div class="panel-body">
													<div class="panel-body">
														<a href="view/image/uni_catwall/module_type1.png" data-toggle="lightbox">
															<img src="view/image/uni_catwall/module_type1.png" class="img-fluid" style="width:100%;height:300px">
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="panel panel-default">
											<div class="panel-heading" id="headingSix" role="tab">
												<h4 class="panel-title"><a aria-controls="collapseSix" aria-expanded="false" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseSix">Тип отображения подкатегорий #3 </a></h4>
											</div>
											<div aria-labelledby="headingSix" class="panel-collapse collapse" id="collapseSix" role="tabpanel">
												<div class="panel-body">
													<a href="view/image/uni_catwall/subcat_type_inline.png" data-toggle="lightbox">
														<img src="view/image/uni_catwall/subcat_type_inline.png" class="img-fluid" style="width:100%;height:300px">
													</a>
												</div>
											</div>
										</div>
										<div class="panel panel-default">
											<div class="panel-heading" id="headingSix" role="tab">
												<h4 class="panel-title"><a aria-controls="collapseSeven" aria-expanded="false" class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapseSeven">Изменение вывода категорий перетаскиванием </a></h4>
											</div>
											<div aria-labelledby="headingSix" class="panel-collapse collapse" id="collapseSeven" role="tabpanel">
												<div class="panel-body">
													<h4 class="text-warning"> Внимание : доступно после сохранения настроек !</h4>
													<a href="view/image/uni_catwall/cat_moove.gif" data-toggle="lightbox">
														<img src="view/image/uni_catwall/cat_moove.gif" class="img-fluid" style="width:100%;height:300px">
													</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane active" id="tab-settings">
						<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-uni-catwall" class="form-horizontal">
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
								<div class="col-sm-10">
									<input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
									<div class="form-text">
										<?php echo $text_name_help; ?>
									</div>
									<?php if ($error_name) { ?>
										<div class="text-danger"><?php echo $error_name; ?></div>
									<?php } ?>
								</div>
							</div> 
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-show"><?php echo $entry_show_name; ?></label>
								<div class="col-sm-10">
									<select name="show_name" id="input-show" class="form-control">
										<?php if ($show_name) { ?>
											<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
											<option value="0"><?php echo $text_disabled; ?></option>
											<?php } else { ?>
											<option value="1"><?php echo $text_enabled; ?></option>
											<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-category"><span data-toggle="tooltip" title="<?php echo $help_category; ?>"><?php echo $entry_category; ?></span></label>
								<div class="col-sm-10">
									<input type="text" name="category" value="" placeholder="<?php echo $entry_category; ?>" id="input-category" class="form-control" />
									<div id="catwall-category" class="well well-sm" style="height: 150px; overflow: auto;">
										<?php foreach ($categorys as $category) { ?>
											<div id="catwall-category<?php echo $category['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $category['name']; ?>
												<input type="hidden" name="category[]" value="<?php echo $category['category_id']; ?>" />
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-subcat"><span data-toggle="tooltip" title="<?php echo $text_subcat_help; ?>"><?php echo $entry_subcat; ?></span></label>
								<div class="col-sm-10">
									<select name="show_subcat" id="input-subcat" class="form-control">
										<?php if ($show_subcat) { ?>
											<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
											<option value="0"><?php echo $text_disabled; ?></option>
											<?php } else { ?>
											<option value="1"><?php echo $text_enabled; ?></option>
											<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-subcat-limit"><?php echo $entry_subcat_limit; ?></label>
								<div class="col-sm-4">
									<input type="text" name="subcat_limit" value="<?php echo $subcat_limit; ?>" placeholder="<?php echo $entry_subcat_limit; ?>" id="input-subcat-limit" class="form-control" />
									<div class="form-text">
										<?php echo $text_subcat_limit_help; ?>
									</div>
								</div>
								<label class="col-sm-2 control-label" for="input-style"><span data-toggle="tooltip" title="<?php echo $help_style; ?>"><?php echo $entry_style; ?></span></label>
								<div class="col-sm-4">
									<select name="uni_catwall_style" id="input-style" class="form-control">
										<option value="0" <?php echo($uni_catwall_style == 0 ? 'selected="selected"' : '') ?>><?php echo $text_style_0; ?></option>
										<option value="1" <?php echo($uni_catwall_style == 1 ? 'selected="selected"' : '') ?>><?php echo $text_style_1; ?></option>
										<option value="2" <?php echo($uni_catwall_style == 2 ? 'selected="selected"' : '') ?>><?php echo $text_style_2; ?></option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-width"><?php echo $entry_width; ?></label>
								<div class="col-sm-4">
									<input type="text" name="width" value="<?php echo $width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-width" class="form-control" />
									<?php if ($error_width) { ?>
										<div class="text-danger"><?php echo $error_width; ?></div>
									<?php } ?>
								</div>
								<label class="col-sm-2 control-label" for="input-height"><?php echo $entry_height; ?></label>
								<div class="col-sm-4">
									<input type="text" name="height" value="<?php echo $height; ?>" placeholder="<?php echo $entry_height; ?>" id="input-height" class="form-control" />
									<?php if ($error_height) { ?>
										<div class="text-danger"><?php echo $error_height; ?></div>
									<?php } ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-status"><?php echo $entry_type; ?></label>
								<div class="col-sm-10">
									<select name="uni_catwall_type" id="input-status" class="form-control">
										<option value="1" <?php echo($uni_catwall_type == 1 ? 'selected="selected"' : '') ?>><?php echo $text_type_1; ?></option>
										<option value="2" <?php echo($uni_catwall_type == 2 ? 'selected="selected"' : '') ?>><?php echo $text_type_2; ?></option>
										<option value="3" <?php echo($uni_catwall_type == 3 ? 'selected="selected"' : '') ?>><?php echo $text_type_3; ?></option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-description"><span data-toggle="tooltip" title="<?php echo $text_description_help; ?>"><?php echo $entry_description; ?></span></label>
								<div class="col-sm-10">
									<select name="show_description" id="input-description" class="form-control">
										<?php if ($show_description) { ?>
											<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
											<option value="0"><?php echo $text_disabled; ?></option>
											<?php } else { ?>
											<option value="1"><?php echo $text_enabled; ?></option>
											<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-description-limit"><?php echo $entry_description_limit; ?></label>
								<div class="col-sm-10">
									<input type="text" name="description_limit" value="<?php echo $description_limit; ?>" placeholder="<?php echo $entry_description_limit; ?>" id="input-description-limit" class="form-control" />
									<div class="form-text">
										<?php echo $text_subcat_description_help; ?>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-status"><?php echo $entry_view_resolution; ?></label>
								<div class="col-sm-10">
									<select name="uni_catwall_view_res" id="input-status" class="form-control">
										<option value="0" <?php echo($uni_catwall_view_res == 0 ? 'selected="selected"' : '') ?>><?php echo $text_choose; ?></option>
										<option value="768" <?php echo($uni_catwall_view_res == 768 ? 'selected="selected"' : '') ?>><?php echo $text_res_768; ?></option>
										<option value="992" <?php echo($uni_catwall_view_res == 992 ? 'selected="selected"' : '') ?>><?php echo $text_res_992; ?></option>
										<option value="1200" <?php echo($uni_catwall_view_res == 1200 ? 'selected="selected"' : '') ?>><?php echo $text_res_1200; ?></option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
								<div class="col-sm-10">
									<select name="status" id="input-status" class="form-control">
										<?php if ($status) { ?>
											<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
											<option value="0"><?php echo $text_disabled; ?></option>
											<?php } else { ?>
											<option value="1"><?php echo $text_enabled; ?></option>
											<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="panel-footer">
					<h4 class="text-warning">Модуль кеширует свои данные, поэтому после изменения настроек, не забывайте очищать системный кеш магазина.</h4>
					<p>В противном случае изменения не будут применяться.</p>
					<h4 class="text-primary">Внимание !</h4>
					<ul>
						<li>Модуль предназачен для работы <strong>только</strong> в шаблоне<a href="https://opencartforum.com/topic/59248-podderzhka-universalnyy-adaptivnyy-shablon-unishop/" target="_blank"> Unishop</a>. Автором данного дополнения является пользователь @Tom.
						<p>https://opencartforum.com/topic/59248-podderzhka-universalnyy-adaptivnyy-shablon-unishop/</p></li>
						<li><a href="https://opencartforum.com/profile/18938-tom/content/?type=downloads_file" target="_blank">Все модули данного автора</a>
						<p>https://opencartforum.com/profile/18938-tom/content/?type=downloads_file</p>
						</li>
					</ul>
					<p class="text-danger">Модуль бесплатный, но я против его использования в составе , каких либо платных дополнений (шаблонов). Так же просьба сохранят мои копирайты. Спасибо!</p>
				</div>
			</div>
		</div>
		<style type="text/css">
			.panel-title > a:before {
			float: right !important;
			font-family: FontAwesome;
			content:"\f068";
			padding-right: 5px;
			}
			.panel-title > a.collapsed:before {
			float: right !important;
			content:"\f067";
			}
			.panel-title > a:hover, 
			.panel-title > a:active, 
			.panel-title > a:focus  {
			text-decoration:none;
			}
		</style>
		<script type="text/javascript"><!--
			$('input[name=\'category\']').autocomplete({
				source: function(request, response) {
					$.ajax({
						url: 'index.php?route=catalog/category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
						dataType: 'json',
						success: function(json) {
							response($.map(json, function(item) {
								return {
									label: item['name'],
									value: item['category_id']
								}
							}));
						}
					});
				},
				select: function(item) {
					$('input[name=\'category\']').val('');
					$('#catwall-category' + item['value']).remove();
					$('#catwall-category').append('<div id="catwall-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="category[]" value="' + item['value'] + '" /></div>');	
				}
			});
			$('#catwall-category').delegate('.fa-minus-circle', 'click', function() {
				$(this).parent().remove();
			});
		//--></script>
		<style type="text/css">
			.ui-sortable-handle {cursor: pointer;}
			.ui-sortable-handle:hover:after {font-family: FontAwesome;content: "\f07d";margin-left: 3px;font-size: 11px;}
			.ui-sortable-handle .fa-minus-circle {color:#f56b6b;}
		</style>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css">
		<script type="text/javascript">
			$(function() {
				$('#catwall-category').sortable();
			});
		</script>
		<script type="text/javascript">
			$(document).on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox();
			});
		</script>
	</div>
	<?php echo $footer; ?>	
<!-- Example for image modal from https://www.w3schools.com/howto/howto_css_modal_images.asp -->