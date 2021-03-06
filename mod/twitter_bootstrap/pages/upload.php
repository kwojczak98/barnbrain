<?php
/**
 * Upload a new file
 * @package ElggFile
 * @elgg-release: 1.9.4
 */
 
elgg_load_library('elgg:file');
$owner = elgg_get_page_owner_entity();

elgg_gatekeeper();
elgg_group_gatekeeper();

$title = elgg_echo('file:add');

// set up breadcrumbs
elgg_push_breadcrumb(elgg_echo('file'), "file/all");
if (elgg_instanceof($owner, 'user')) {
	elgg_push_breadcrumb($owner->name, "file/owner/$owner->username");
} else {
	elgg_push_breadcrumb($owner->name, "file/group/$owner->guid/all");
}
elgg_push_breadcrumb($title);

// create form
$action = elgg_get_config('url').'action/twitter_bootstrap/upload';
$security = elgg_view('input/securitytoken');

$access =  '<label>'.elgg_echo('access').': </label><br />'.elgg_view('input/access', array('name' => 'access[]'));
$tags = '<label>'.elgg_echo('tags').': </label>'.elgg_view('input/tags', array('name' => 'tags[]', 'placeholder' => elgg_echo('tags')));

$file_title = '<label>'.elgg_echo('title').': </label>'.elgg_view('input/text', array('name' => "title[]", 'value' => '{%=file.name%}'));

$description = '<label>'.elgg_echo('description').': </label><br />'.elgg_view('input/plaintext', array('name' => "description[]", 'rows' => 3));



$container_guid = (elgg_extract('container_guid', $vars))? elgg_extract('container_guid', $vars): elgg_get_logged_in_user_guid() ;
$container = elgg_view('input/hidden', array('name' => 'container_guid', 'value' => $container_guid));

elgg_load_css('jquery_fileupload_css');
elgg_load_css('jquery_fileupload_ui_css');

elgg_require_js('main');

$content = <<<HTML
<div class="row">
	<!-- The file upload form used as target for the file upload widget -->
	<form id="fileupload" action="{$action}" method="post" enctype="multipart/form-data">
		{$security}
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Add files...</span>
                    <input type="file" name="files[]" multiple>
                </span>
                <button type="submit" class="btn btn-primary start">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start upload</span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel upload</span>
                </button>
                <button type="button" class="btn btn-danger delete">
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" class="toggle">
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
	</form>
<!-- </div>	-->
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="col-md-3">
            <span class="preview"></span>
        </td>
		
        <td class="col-md-5">
            <p class="hidden">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
			{$file_title}
			<p>
			{$description}
			</p>
		</td>
				
 		<td class="col-md-4">
			<table class="table table-striped">
				<tr>
					<td colspan="2">
						<p class="size">Processing...</p>
					</td>
					<td>
						{% if (!i && !o.options.autoUpload) { %}
							<button class="btn btn-primary start" disabled>
								<i class="glyphicon glyphicon-upload"></i>
								<span>Start</span>
							</button>
						{% } %}
					</td>
					<td>
						{% if (!i) { %}
							<button class="btn btn-warning cancel">
								<i class="glyphicon glyphicon-ban-circle"></i>
								<span>Cancel</span>
							</button>
						{% } %}
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
							<div class="progress-bar progress-bar-success" style="width:0%;"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						{$tags}
					</td>
					<td colspan="2">
						{$access}
						{$container}
					</td>
				</tr>
			</table>
		</td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td class="col-md-3">
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td class="col-md-5">
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
			{% if (file.success) { %}
                <div><span class="label label-success">Success</span> {%=file.success%}</div>
            {% } %}
		</td>
        <td class="col-md-2">
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td class="col-md-2">
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
</div>
HTML;

$body = elgg_view_layout('one_column', array(
	'content' => $content,
	'title' => $title,
	'filter' => '',
));
echo elgg_view_page($title, $body);
