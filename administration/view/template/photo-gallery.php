<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/photogallery/photogallery-c.php';
$title = 'Photogallery';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTableDragNDrop();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/photogallery.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/photogallery.min.js"></script>
<?php endif; ?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" />
<?php loadMainCss(); ?>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Galleries</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>photogallery/new" id="action-new-album">
						<span class="ui-icon ui-icon-plus"></span>
						New Album
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-album">Edit</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-publish-album">
								<span class="ui-icon ui-icon-check"></span>
								Publish
							</a>
						</li>
						<li>
							<a href="#" id="action-unpublish-album">
								<span class="ui-icon ui-icon-radio-off"></span>
								Unpublish
							</a>
						</li>
						<li>
							<a href="#" id="action-feature-album">
								<span class="ui-icon ui-icon-star"></span>
								Feature
							</a>
						</li>
						<li>
							<a href="#" id="action-nofeature-album">
								<span class="ui-icon ui-icon-cancel"></span>
								Disregard
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-album">
						<span class="ui-icon ui-icon-trash"></span>
						Delete
					</a>
				</li>
				<li>
					<a href="#">Screen Options</a>
					<ul>
						<?php foreach($optionsForPage as $optionForPage): ?>
						<li>
							<a id="screen-option-<?php echo hsc($optionForPage['id']); ?>" class="screen-option action-no-close" href="#">
								<span class="<?php if(in_array($optionForPage['id'], $userOptions)): ?>ui-icon ui-icon-check<?php endif; ?>"></span>
								<?php echo hsc($optionForPage['option_name']); ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</li>
				<li><a href="#">Help</a></li>
				<li>
					<a href="#">About</a>
				</li>
			</ul>
			<div class="search-wrap">
				<form id="album-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="album-search" class="med-small-text overview-search" name="albumToSearch" placeholder="Search by album" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitAlbumSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-69" <?php if(!in_array(69, $userOptions)): ?>style="display: none"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new-album">
						<a class="" href="<?php echo HTTP_ADMIN; ?>photogallery/new/">
							<span class="ui-icon ui-icon-plus"> </span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit-album">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"> </span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-publish-album">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li id="toolbar-unpublish-album">
						<a href="#">
							<span class="ui-icon ui-icon-radio-off"> </span>
							<span class="toolbar-text">Unpublish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li>
						<a href="#" id="toolbar-feature-album">
							<span class="ui-icon ui-icon-star"> </span>
							<span class="toolbar-text">Feature</span>
						</a>
					</li>
					<li>
						<a href="#" id="toolbar-nofeature-album">
							<span class="ui-icon ui-icon-cancel"> </span>
							<span class="toolbar-text">Disregard</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-album">
						<a href="#">
							<span class="ui-icon ui-icon-trash"> </span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options-album">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"> </span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-album">
						<a href="#">
							<span class="ui-icon ui-icon-help"> </span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="clearfix"></div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="<?php echo HTTP_ADMIN; ?>photogallery" method="post">
					<div class="element filter-menu fltlft">
						<div class="element-top">
							<h3 class="element-head">Filter By</h3>
						</div>
						<div class="element-body">
							<div class="element-body-content">
								<select name="stateFilter" class="user-filter" onChange="Javascript: CMS.submitButton('gallery-album', 'stateFilter');">
									<option selected="selected" disabled="disabled">--State--</option>
									<option value="1">Published</option>
									<option value="0">Unpublished</option>
								</select>
								<select name="featuredFilter" class="user-filter" onChange="Javascript: CMS.submitButton('gallery-album', 'featuredFilter');">
									<option selected="selected" disabled="disabled">--Featured--</option>
									<option value="1">Featured</option>
									<option value="0">Not Featured</option>
								</select>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="gallery-action" name="galleryAction" />
					</div>
					<div class="links links-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
					<div class="clearfix"></div>
					<?php if(isset($albumList)): ?>
					<table id="album-overview" class="overview">
						<thead>
							<tr class="overview-top nodrop nodrag">
								<th class="width1pcnt">
									<input id="selectAll" type="checkbox" class="overview-check-all" />
								</th>
								<th class="width10pcnt">Thumbnail</th>
								<th class="left">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'search', 'title');?>">
									<?php elseif(isset($_GET['state'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'state', 'title');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'featured', 'title');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', '', 'title');?>">
									<?php endif; ?>
									Album <?php echo TableSorter::displaySortIcon('title', 'asc'); ?>
									</a>
								</th>
								<th class="width5pcnt option-35" <?php if(!in_array(35, $userOptions)): ?> style="display: none;" <?php endif; ?>># of Photos</th>
								<th class="width5pcnt option-36" <?php if(!in_array(36, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'search', 'state');?>">
									<?php elseif(isset($_GET['state'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'state', 'state');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'featured', 'state');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', '', 'state');?>">
									<?php endif; ?>
									State <?php echo TableSorter::displaySortIcon('state', 'asc'); ?></a>
								</th>
								<th class="width5pcnt option-37" <?php if(!in_array(37, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'search', 'featured');?>">
									<?php elseif(isset($_GET['state'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'state', 'featured');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'featured', 'featured');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', '', 'featured');?>">
									<?php endif; ?>
									Featured <?php echo TableSorter::displaySortIcon('featured', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt option-38" <?php if(!in_array(38, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'search', 'order_of_item');?>">
									<?php elseif(isset($_GET['state'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'state', 'order_of_item');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'featured', 'order_of_item');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', '', 'order_of_item');?>">
									<?php endif; ?>
									Ordering <?php echo TableSorter::displaySortIcon('order_of_item', 'asc', TRUE); ?>
									</a>
								</th>
								<th class="width5pcnt option-39" <?php if(!in_array(39, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'search', 'hits');?>">
									<?php elseif(isset($_GET['state'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'state', 'hits');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'featured', 'hits');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', '', 'hits');?>">
									<?php endif; ?>
									Hits <?php echo TableSorter::displaySortIcon('hits', 'desc'); ?>
									</a>
								</th>
								<th class="width5pcnt option-40" <?php if(!in_array(40, $userOptions)): ?> style="display: none;" <?php endif; ?>>Photos</th>
								<th class="width5pcnt option-41" <?php if(!in_array(41, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'search', 'id');?>">
									<?php elseif(isset($_GET['state'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'state', 'id');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', 'featured', 'id');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/photogallery', '', 'id');?>">
									<?php endif; ?>
									Id <?php echo TableSorter::displaySortIcon('id', 'asc'); ?>
									</a>
								</th>
							</tr>
						</thead>
						<tbody class="content">
						<?php foreach($albumList as $num=>$album): ?>
							<tr class="overview-row-<?php echo hsc($album['id']);?>" id="album_<?php echo hsc($album['id']); ?>">
								<td>
									<input id="cb-<?php echo hsc($num); ?>" type="checkbox" name="albumCheck[]" class="overview-check" value="<?php echo hsc($album['id']);?>" />
									<input type="hidden" name="id" value="<?php echo hsc($album['id']); ?>" />
								</td>
								<td>
									<a href="<?php echo HTTP_ADMIN . "photogallery/" . $album['alias'];?>" target="mainIframe">
										<?php if(!empty($album['image'])): ?>
										<img class="album-thumb" src="<?php echo hsc(HTTP_GALLERY . 'admin-thumb-' . $album['image']); ?>" alt="<?php echo hsc($album['title']); ?>" />
										<?php else: ?>
										<?php echo hsc($album['title']); ?>
										<?php endif; ?>
									</a>
								</td>
								<td class="left">
									<a href="<?php echo HTTP_ADMIN . "photogallery/" . $album['alias'] . "/edit/"; ?>" target="mainIframe">
										<?php echo hsc($album['title']); ?>
									</a>
								</td>
								<td class="option-35" <?php if(!in_array(35, $userOptions)): ?> style="display: none;" <?php endif; ?>><?php echo hsc($gallery->getNumOfPhotos($album['id'])); ?></td>
								<td class="option-36" <?php if(!in_array(36, $userOptions)): ?> style="display: none;" <?php endif; ?> id="state-<?php echo hsc($album['id']); ?>">
									<?php if($album['state'] == 1 ): ?>
									<span class="icon-20-check icon-20-spacing"> </span>
									<?php else: ?>
									<span class="icon-20-disabled icon-20-spacing"> </span>
									<?php endif; ?>
								</td>
								<td class="option-37" <?php if(!in_array(37, $userOptions)): ?> style="display: none;" <?php endif; ?> id="featured-<?php echo hsc($album['id']); ?>">
									<?php if($album['featured'] == 1): ?>
									<span class="icon-20-star icon-20-spacing"> </span>
									<?php else: ?>
									<span class="icon-20-gray-disabled icon-20-spacing"> </span>
									<?php endif; ?>
								</td>
								<td class="option-38" <?php if(!in_array(38, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<input type="text" name="orderNumber" size="1" maxlength="4" value="<?php echo hsc($album['order_of_item']); ?>" />
								</td>
								<td class="option-39" <?php if(!in_array(39, $userOptions)): ?> style="display: none;" <?php endif; ?>><?php echo hsc($album['hits']); ?></td>
								<td class="option-40" <?php if(!in_array(40, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<a href="<?php echo HTTP_ADMIN . 'photogallery/' . $album['alias'];?>" title="View photos in this album.">View</a>
								</td>
								<td class="option-41" <?php if(!in_array(41, $userOptions)): ?> style="display: none;" <?php endif; ?>><?php echo hsc($album['id']); ?></td>
							</tr>
							<?php endforeach; ?>
							<?php if(empty($albumList)): ?>
							<tr class="overview-row1">
								<td colspan="11">
									<?php if(isset($_GET['search'])): ?>
									Your search did not match any records.
									<?php elseif(isset($_GET['state']) || isset($_GET['featured'])): ?>
									No albums available with the current filter.
									<?php else: ?>
									No albums have been created yet.
									<?php endif; ?>
								</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
					<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
					<?php endif; ?>
				</form>
			</div>
			<div id="options-form" title="Photogallery Options">
				<div class="panel">
					<div class="panel-column">
						<form action="<?php echo HTTP_ADMIN; ?>photogallery" method="post">
							<table id="template-list" class="overview fltlft width100pcnt">
								<thead>
									<tr class="option-top">
										<th class="left">Template Name</th>
										<th>Thumbnail Width</th>
										<th>Thumbnail Height</th>
										<th>Image Width</th>
										<th>Image Height</th>
										<th>Type</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($templates as $key => $template): ?>
									<?php $num = ($key % 2) + 1; ?>
									<tr id="template-<?php echo hsc($template['id']); ?>" class="overview-row<?php echo $num; ?>">
										<td><?php echo hsc($template['template_name']); ?></td>
										<td>
											<input type="text" id="thumb-width-<?php echo hsc($template['id']); ?>" size="5" value="<?php echo hsc($template['thumbnail_width']); ?>" />
										</td>
										<td>
											<input type="text" id="thumb-height-<?php echo hsc($template['id']); ?>" size="5" value="<?php echo hsc($template['thumbnail_height']); ?>" />
										</td>
										<td>
											<input type="text" id="image-width-<?php echo hsc($template['id']); ?>" size="5" value="<?php echo hsc($template['image_width']); ?>" />
										</td>
										<td>
											<input type="text" id="image-height-<?php echo hsc($template['id']); ?>" size="5" value="<?php echo hsc($template['image_height']); ?>" />
										</td>
										<td>
											<select id="template-type-<?php echo hsc($template['id']); ?>">
											<?php foreach($templateTypes as $templateType): ?>
												<?php if($template['type'] === $templateType): ?>
												<option value="<?php echo hsc($template['type']); ?>" selected="selected">
													<?php echo hsc($template['type']); ?>
												</option>
												<?php else: ?>
												<option value="<?php echo hsc($templateType); ?>">
													<?php echo hsc($templateType); ?>
												</option>
												<?php endif; ?>
											<?php endforeach; ?>
											</select>
										</td>
										<td>
											<p>
												<a id="save-template-<?php echo hsc($template['id']); ?>" class="save-template" href="#">Save</a>
												<span> | </span> 
												<a id="delete-template-<?php echo hsc($template['id']); ?>" class="delete-template" href="#">Delete</a>
											</p>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="7">
											<ul>
												<li>
													<div id="add-template" class="adder vert-space">
														<h4>+ Add Template</h4>
													</div>
													<div id="add-template-form">
														<form action="<?php echo HTTP_ADMIN; ?>photogallery" method="post">
															<label class="adder-label">Template Name
															<input class="adder-input" type="text" id="new-template-name" name="templateName" size="30" />
															</label>
															<label class="adder-label">Thumbnail Width
															<input class="adder-input" type="text" id="new-thumb-width" name="thumbWidth" size="30" />
															</label>
															<label class="adder-label">Thumbnail Height
															<input class="adder-input" type="text" id="new-thumb-height" name="thumbHeight" size="30" />
															</label>
															<label class="adder-label">Image Width
															<input class="adder-input" type="text" id="new-image-width" name="imageWidth" size="30" />
															</label>
															<label class="adder-label">Image Height
															<input class="adder-input" type="text" id="new-image-height" name="imageHeight" size="30" />
															</label>
															<label class="adder-label">Template Type
															<select class="adder-input" id="template-type">
																<?php foreach($templateTypes as $templateType): ?>
																<option value="<?php echo hsc($templateType); ?>">
																	<?php echo hsc($templateType); ?>
																</option>
																<?php endforeach; ?>
															</select>
															</label>
															<button id="add-new-template" class="rb-btn">Create Template</button>
														</form>
													</div>
												</li>
											</ul>
										</td>
									</tr>
								</tfoot>
							</table>
						</form>
						<div class="clearfix"> </div>
					</div>
				</div>
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>photogallery" method="post">
								<?php foreach($optionsForPage as $optionForPage): ?>
									<label>
										<input type="checkbox" id="option-<?php echo hsc($optionForPage['id']); ?>" class="optionChange"
											   <?php if(in_array($optionForPage['id'], $userOptions)): ?>
											   checked="checked"
											   <?php endif; ?>
										/>
									<?php echo hsc($optionForPage['option_name']); ?>
									</label>
								<?php endforeach; ?>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
