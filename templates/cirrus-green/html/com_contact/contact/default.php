<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$cparams = JComponentHelper::getParams('com_media');
?>
<div class="titulo-contato"><h2>Envie-nos suas dúvidas ou sugestões. Será um prazer atendê-lo</h2></div>
<div class="contact<?php echo $this->pageclass_sfx?>">

	
	
	<?php if ($this->params->get('show_contact_list') && count($this->contacts) > 1) : ?>
		<form action="#" method="get" name="selectForm" id="selectForm">
			<?php echo JText::_('COM_CONTACT_SELECT_CONTACT'); ?>
			<?php echo JHtml::_('select.genericlist',  $this->contacts, 'id', 'class="inputbox" onchange="document.location.href = this.value"', 'link', 'name', $this->contact->link);?>
		</form>
	<?php endif; ?>

	<?php if ($this->params->get('show_tags', 1) && !empty($this->item->tags)) : ?>
		<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
		<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
	<?php endif; ?>

	<?php  if ($this->params->get('presentation_style') == 'sliders'):?>
		<div class="accordion" id="accordionContact">
			<div class="accordion-group">
				
				<div id="basic-details" class="accordion-body collapse in">
					<div class="accordion-inner">
	<?php endif; ?>
	
	<?php if ($this->contact->image && $this->params->get('show_image')) : ?>
		<div class="thumbnail pull-right">
			<?php echo JHtml::_('image', $this->contact->image, JText::_('COM_CONTACT_IMAGE_DETAILS'), array('align' => 'middle')); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->contact->con_position && $this->params->get('show_position')) : ?>
		<dl class="contact-position dl-horizontal">
			<dd>
				<?php echo $this->contact->con_position; ?>
			</dd>
		</dl>
	<?php endif; ?>

	<?php echo $this->loadTemplate('address'); ?>

	<?php if ($this->params->get('allow_vcard')) :	?>
		<?php echo JText::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
			<a href="<?php echo JRoute::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->contact->id . '&amp;format=vcf'); ?>">
			<?php echo JText::_('COM_CONTACT_VCARD');?></a>
	<?php endif; ?>
	<?php if ($this->params->get('presentation_style') == 'sliders'):?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->params->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>
		<?php  echo $this->loadTemplate('form');  ?>
	<?php endif; ?>

	<?php if ($this->params->get('show_links')) : ?>
		<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>

	<?php if ($this->params->get('show_articles') && $this->contact->user_id && $this->contact->articles) : ?>
			<?php if ($this->params->get('presentation_style') == 'sliders'):?>
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="accordionContact" href="#display-articles">
					<?php echo JText::_('JGLOBAL_ARTICLES');?>
					</a>
				</div>
				<div id="display-articles" class="accordion-body collapse">
					<div class="accordion-inner">
			<?php endif; ?>
			<?php if  ($this->params->get('presentation_style') == 'plain'):?>
			<?php echo '<h3>'. JText::_('JGLOBAL_ARTICLES').'</h3>'; ?>
			<?php endif; ?>
			<?php echo $this->loadTemplate('articles'); ?>
			<?php if ($this->params->get('presentation_style') == 'sliders'):?>
					</div>
				</div>
			</div>
			<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->params->get('show_profile') && $this->contact->user_id && JPluginHelper::isEnabled('user', 'profile')) : ?>
		<?php if ($this->params->get('presentation_style') == 'sliders'):?>
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="accordionContact" href="#display-profile">
					<?php echo JText::_('COM_CONTACT_PROFILE');?>
					</a>
				</div>
				<div id="display-profile" class="accordion-body collapse">
					<div class="accordion-inner">
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') == 'plain'):?>
			<?php echo '<h3>'. JText::_('COM_CONTACT_PROFILE').'</h3>'; ?>
		<?php endif; ?>
		<?php echo $this->loadTemplate('profile'); ?>
		<?php if ($this->params->get('presentation_style') == 'sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->contact->misc && $this->params->get('show_misc')) : ?>
		<?php if ($this->params->get('presentation_style') == 'sliders'):?>
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="accordionContact" href="#display-misc">
					<?php echo JText::_('COM_CONTACT_OTHER_INFORMATION');?>
					</a>
				</div>
				<div id="display-misc" class="accordion-body collapse">
					<div class="accordion-inner">
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') == 'plain'):?>
			<?php echo '<h3>'. JText::_('COM_CONTACT_OTHER_INFORMATION').'</h3>'; ?>
		<?php endif; ?>
				<div class="contact-miscinfo">
					<dl class="dl-horizontal">
						<dt>
							<span class="<?php echo $this->params->get('marker_class'); ?>">
								<?php echo $this->params->get('marker_misc'); ?>
							</span>
						</dt>
						<dd>
							<span class="contact-misc">
								<?php echo $this->contact->misc; ?>
							</span>
						</dd>
					</dl>
				</div>
		<?php if ($this->params->get('presentation_style') == 'sliders'):?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->params->get('presentation_style') == 'sliders'):?>
		</div>
	<?php endif; ?>
