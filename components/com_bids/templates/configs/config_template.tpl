{* Configuration for Auction Factory Template *}
{* to set up the template please edit config.tpl *}
{* change this file only if you know what you are doing *}
{config_load file="config.tpl"}


{* read get/post *}

{assign var=t_display_style value="list"}

{if isset($smarty.request.liststyle)}
    {assign var=t_display_style value=$smarty.request.liststyle}
{elseif isset($smarty.session.t_display_style)}
    {assign var=t_display_style value=$smarty.session.t_display_style}
{/if}
{php}
    $_SESSION['t_display_style']=$this->get_template_vars('t_display_style');
    $this->_config[0]["vars"]["display_style"]=$this->get_template_vars('t_display_style');
    $this->_config[1]["vars"]["display_style"]=$this->get_template_vars('t_display_style');    
{/php}

