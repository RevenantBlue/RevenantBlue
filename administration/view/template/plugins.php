<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/plugins/plugins-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Plugins';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
?>
<?php loadMainCss(); ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Plugins</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
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
				<li>
					<a href="#">Help</a>
				</li>
				<li>
					<a href="#">About</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-90" <?php if(!in_array(90, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-options-plugins">
						<a href="#">
							<span class="ui-icon ui-icon-gear"></span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-plugins">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
				<div>
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="submitComment" name="submitComment" value="" />
				</div>
				<div class="clear"></div>
				<table id="overview" class="plugin-padding">
					<tr class="overview-top">
						<th class="width1pcnt">
							<input id="selectAll" type="checkbox" class="overview-check-all" />
						</th>
						<th class="left">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/plugins/', '', 'plugin_name'); ?>">
							Module <?php echo TableSorter::displaySortIcon('module_name', 'asc'); ?>
							</a>
						</th>
						<th class="width10pcnt">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/plugins/', '', 'version');?>">
							Version <?php echo TableSorter::displaySortIcon('version', 'asc'); ?>
							</a>
						</th>
						<th class="width50pcnt left">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/plugins/', '', 'description');?>">
							Description <?php echo  TableSorter::displaySortIcon('description', 'asc'); ?>
							</a>
						</th>
						<th class="left width30pcnt">
							Operations
						</th>
					</tr>
				<?php if(!empty($pluginList)): ?>
					<?php foreach($pluginList as $plugin): ?>
					<tr>
						<td>
							<input id="cb-<?php echo hsc($plugin['id']); ?>" type="checkbox" class="overview-check" name="pluginCheck[]" value="<?php echo hsc($plugin['id']);?>" />
						</td>
						<td class="left">
							<a href="<?php echo HTTP_ADMIN . 'plugins/' . hsc($plugin['alias']); ?>"><?php echo hsc($plugin['plugin_name']); ?></a>
						</td>
						<td>
							<?php echo hsc($plugin['version']); ?>
						</td>
						<td class="left">
							<?php echo hsc($plugin['description']); ?>
						</td>
						<td></td>
					</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="5">
							There are no plugins installed.
						</td>
					</tr>
				<?php endif; ?>
				</table>
				<div class="links"><?php if(isset($pager)) echo $pager->menu; ?></div>
			</form>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
