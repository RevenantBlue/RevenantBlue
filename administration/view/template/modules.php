<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/modules/modules-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Modules';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
?>
<?php loadMainCss(); ?>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="toolbar-box">
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-options">
						<a href="#"><span class="icon-40-gear"> </span>Options</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help">
						<a href="#"><span class="icon-40-help"> </span>Help</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div> <!-- End Toolbar -->
			<div class="page-title">
				<h2>Modules</h2>
			</div>
		</div><!-- End Toolbar Box -->
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
				<div>
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="submitComment" name="submitComment" value="" />
				</div>
				<div class="clear"></div>
				<table id="overview" class="module-padding">
					<tr class="overview-top">
						<th class="width1pcnt">
							<input id="selectAll" type="checkbox" onClick="Javascript: CMS.checkAll();" />
						</th>
						<th class="left">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/', '', 'module_name'); ?>">
							Module <?php echo TableSorter::displaySortIcon('module_name', 'asc'); ?>
							</a>
						</th>
						<th class="width10pcnt">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/', '', 'version');?>">
							Version <?php echo TableSorter::displaySortIcon('version', 'asc'); ?>
							</a>
						</th>
						<th class="width50pcnt left">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/', '', 'description');?>">
							Description <?php echo  TableSorter::displaySortIcon('description', 'asc'); ?>
							</a>
						</th>
						<th class="left width30pcnt">
							<p>Operations</p>
						</th>
					</tr>
				<?php if(!empty($moduleList)): ?>
				<?php foreach($moduleList as $num=>$module): ?>
					<?php $class = ($num % 2) + 1; ?>
					<tr class="overview-row<?php echo $class; ?>">
						<td>
							<input id="cb<?php echo hsc($num); ?>" type="checkbox" name="commentCheck[]" value="<?php echo hsc($module['id']);?>" onClick="Javascript: CMS.isChecked(this);" />
						</td>
						<td class="left">
							<a href="<?php echo HTTP_ADMIN . 'modules/' . hsc($module['alias']); ?>"><?php echo hsc($module['module_name']); ?></a>
						</td>
						<td><?php echo hsc($module['version']); ?></td>
						<td class="left"><?php echo hsc($module['description']); ?></td>
						<td class="left"></td>
					</tr>
				<?php endforeach; ?>
				<?php else: ?>
					<tr class="overview-row1">
						<td colspan="5"><p>There are no modules installed currently</p></td>
					</tr>
				<?php endif; ?>
				</table>
				<div class="links"><?php if(isset($pager)) echo $pager->menu; ?></div>
				</form>
		</div>
	</div>
<br />
</div>
<?php displayFooter(); ?>
