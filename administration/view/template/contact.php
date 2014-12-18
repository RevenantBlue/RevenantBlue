<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/modules/contact/contact-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Modules | Contact';
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
		<div id="toolbar-box" class="clearfix">
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-delete">
						<a onclick="Javascript: if(boxesChecked == 0) { alert('No contacts selected, please select a contact from the table below and try again.'); } else { CMS.submitButton('contact', 'delete'); } return false;" href="#"><span class="icon-40-trashminus"> </span>Delete</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options">
						<a href="#"><span class="icon-40-gear"> </span>Options</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help">
						<a href="#"><span class="icon-40-help"> </span>Help</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
				<div>
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
				</div>
				<div class="clear"></div>
				<table id="overview">
					<tr class="overview-top">
						<th class="width1pcnt">
							<input id="selectAll" type="checkbox" onClick="Javascript: CMS.checkAll();" />
						</th>
						<th class="left">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/contact/', '', 'name'); ?>">
							Name <?php echo TableSorter::displaySortIcon('name', 'asc'); ?>
							</a>
						</th>
						<th class="width10pcnt">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/contact/', '', 'create_date');?>">
							Date <?php echo TableSorter::displaySortIcon('create_date', 'asc', TRUE); ?>
							</a>
						</th>
						<th class="width15pcnt">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/contact/', '', 'email'); ?>">
							Email <?php echo TableSorter::displaySortIcon('create_date', 'asc'); ?>
							</a>
						</th>
						<th class="width50pcnt left">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/contact/', '', 'message');?>">
							Message <?php echo  TableSorter::displaySortIcon('description', 'asc'); ?>
							</a>
						</th>
						<th class="width1pcnt">
							<a class="link" href="<?php echo TableSorter::sortLink('admin/modules/contact/', '', 'id');?>">
							Id <?php echo  TableSorter::displaySortIcon('id', 'asc'); ?>
							</a>
						</th>
					</tr>
				<?php if(!empty($contactList)): ?>
				<?php foreach($contactList as $num=>$contact): ?>
					<?php $class = ($num % 2) + 1; ?>
					<tr class="overview-row<?php echo $class; ?>">
						<td>
							<input id="cb<?php echo hsc($num); ?>" type="checkbox" name="contactCheck[]" value="<?php echo hsc($contact['id']);?>" onClick="Javascript: CMS.isChecked(this);" />
						</td>
						<td class="left"><?php echo hsc($contact['name']); ?></td>
						<td><?php echo hsc($contact['create_date']); ?></td>
						<td><?php echo hsc($contact['email']); ?></td>
						<td class="left"><?php echo hsc($contact['message']); ?></td>
						<td><?php echo hsc($contact['id']); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php else: ?>
					<tr class="overview-row1">
						<td colspan="5"><p>There are no messages.</p></td>
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
