<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$templateparams = $app->getTemplate(true)->params;
$images = json_decode($this->item->images);
$urls = json_decode($this->item->urls);
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// Create shortcut to parameters.
$params = $this->item->params;

?>
<article class="item-page<?= $this->pageclass_sfx ?>">
	<?php if (!empty($images->image_intro)) : ?>
		<div class="intro-image">
			<?php
			$aHref = $closeA = '';
			if (!empty($urls->urla)) {
				$aHref = '<a href="' . $urls->urla . '" >';
				$closeA = '</a>';
			} ?>
			<?= $aHref ?>
			<img src="<?= $images->image_intro ?>" alt="<?= $images->image_intro_alt ?>" />
			<?= $closeA ?>
		</div>
	<?php endif; ?>
	<?php if ($this->params->get('show_page_heading')) : ?>

		<?php if ($this->params->get('show_page_heading') and $params->get('show_title')) : ?>
			<hgroup>
			<?php endif; ?>
			<h1>
				<?= $this->escape($this->params->get('page_heading')); ?>
			</h1>
		<?php endif; ?>
		<?php
		if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative) {
			echo $this->item->pagination;
		}
		?>
		<?php if ($params->get('show_title')) : ?>
			<h2>
				<?= $this->escape($this->item->title); ?>
			</h2>
		<?php endif; ?>
		<?php if ($this->params->get('show_page_heading') and $params->get('show_title')) : ?>
			</hgroup>
		<?php endif; ?>

		<?php if ($params->get('access-edit') ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
			<ul class="actions">
				<?php if (!$this->print) : ?>
					<?php if ($params->get('show_print_icon')) : ?>
						<li class="print-icon">
							<?= JHtml::_('icon.print_popup', $this->item, $params, array(), true); ?>
						</li>
					<?php endif; ?>

					<?php if ($params->get('show_email_icon')) : ?>
						<li class="email-icon">
							<?= JHtml::_('icon.email', $this->item, $params, array(), true); ?>
						</li>
					<?php endif; ?>
					<?php if ($this->user->authorise('core.edit', 'com_content.article.' . $this->item->id)) : ?>
						<li class="edit-icon">
							<?= JHtml::_('icon.edit', $this->item, $params, array(), true); ?>
						</li>
					<?php endif; ?>
				<?php else : ?>
					<li>
						<?= JHtml::_('icon.print_screen', $this->item, $params, array(), true); ?>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

		<?php if (!$params->get('show_intro')) :
			echo $this->item->event->afterDisplayTitle;
		endif; ?>

		<?= $this->item->event->beforeDisplayContent; ?>

		<?php $useDefList = (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_parent_category'))
			or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))
			or ($params->get('show_hits'))); ?>

		<?php if ($useDefList) : ?>
			<dl class="article-info">
				<dt class="article-info-term"><?php echo JText::_('COM_CONTENT_ARTICLE_INFO'); ?></dt>
			<?php endif; ?>
			<?php if ($params->get('show_parent_category') && $this->item->parent_slug != '1:root') : ?>
				<dd class="parent-category-name">
					<?php $title = $this->escape($this->item->parent_title);
					$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_slug)) . '">' . $title . '</a>'; ?>
					<?php if ($params->get('link_parent_category') and $this->item->parent_slug) : ?>
						<?= JText::sprintf('COM_CONTENT_PARENT', $url); ?>
					<?php else : ?>
						<?= JText::sprintf('COM_CONTENT_PARENT', $title); ?>
					<?php endif; ?>
				</dd>
			<?php endif; ?>
			<?php if ($params->get('show_category')) : ?>
				<dd class="category-name">
					<?php $title = $this->escape($this->item->category_title);
					$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)) . '">' . $title . '</a>'; ?>
					<?php if ($params->get('link_category') and $this->item->catslug) : ?>
						<?= JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
					<?php else : ?>
						<?= JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
					<?php endif; ?>
				</dd>
			<?php endif; ?>
			<?php if ($params->get('show_create_date')) : ?>
				<dd class="create">
					<?= JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2'))); ?>
				</dd>
			<?php endif; ?>
			<?php if ($params->get('show_modify_date')) : ?>
				<dd class="modified">
					<?= JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
				</dd>
			<?php endif; ?>
			<?php if ($params->get('show_publish_date')) : ?>
				<dd class="published">
					<?= JText::sprintf(JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
				</dd>
			<?php endif; ?>
			<?php if ($params->get('show_author') && !empty($this->item->author)) : ?>
				<dd class="createdby">
					<?php $author = $this->item->author; ?>
					<?php $author = ($this->item->created_by_alias ? $this->item->created_by_alias : $author); ?>

					<?php if (!empty($this->item->contactid) &&  $params->get('link_author') == true) : ?>
						<?= JText::sprintf(
							'COM_CONTENT_WRITTEN_BY',
							JHtml::_('link', JRoute::_('index.php?option=com_contact&view=contact&id=' . $this->item->contactid), $author)
						); ?>

					<?php else : ?>
						<?= JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
					<?php endif; ?>
				</dd>
			<?php endif; ?>
			<?php if ($params->get('show_hits')) : ?>
				<dd class="hits">
					<?= JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?>
				</dd>
			<?php endif; ?>
			<?php if ($useDefList) : ?>
			</dl>
		<?php endif; ?>

		<?php if (isset($this->item->toc)) : ?>
			<?= $this->item->toc; ?>
		<?php endif; ?>

		<?php if (isset($images->image_fulltext) and !empty($images->image_fulltext)) : ?>
			<?php $imgfloat = (empty($images->float_fulltext)) ? $params->get('float_fulltext') : $images->float_fulltext; ?>

			<div class="img-fulltext-<?= htmlspecialchars($imgfloat); ?>">
				<img <?php if ($images->image_fulltext_caption) :
							echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_fulltext_caption) . '"';
						endif; ?> src="<?= htmlspecialchars($images->image_fulltext); ?>" alt="<?= htmlspecialchars($images->image_fulltext_alt); ?>" />
			</div>
		<?php endif; ?>



		<?php
		if (!empty($this->item->pagination) and $this->item->pagination and !$this->item->paginationposition and !$this->item->paginationrelative) :
			echo $this->item->pagination;
		endif;
		?>
		<?= $this->item->text; ?>

		<p class="link-c">
			<?php if (!empty($urls->urlc)) :
				$urlLinkText = (!empty($urls->urlctext)) ? $urls->urlctext : 'Saiba mais...';
			?>
				<a href="<?= $urls->urlc ?>" class="readmore-link-c"><?= $urlLinkText ?></a>
			<?php endif; ?>
		</p>

		<?php
		if (!empty($this->item->pagination) and $this->item->pagination and $this->item->paginationposition and !$this->item->paginationrelative) :
			echo $this->item->pagination; ?>
		<?php endif; ?>

		<?php
		if (!empty($this->item->pagination) and $this->item->pagination and $this->item->paginationposition and $this->item->paginationrelative) :
			echo $this->item->pagination; ?>
		<?php endif; ?>
		<?= $this->item->event->afterDisplayContent; ?>
</article>