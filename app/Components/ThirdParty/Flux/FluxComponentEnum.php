<?php

namespace App\Components\ThirdParty\Flux;

enum FluxComponentEnum: string
{
    // general
    case CARD = 'card';
    case HEADING = 'heading';
    case TEXT = 'text';
    case BUTTON = 'button';
    case BADGE = 'badge';
    case LINK = 'link';
    case ICON = 'icon';
    case SEPARATOR = 'separator';
    case SPACER = 'spacer';

    // forms
    case LABEL = 'label';
    case TEXT_INPUT = 'input';
    case TEXT_FILE = 'file';
    case TEXTAREA = 'textarea';
    case SELECT = 'select';
    case OPTION = 'select.option';
    case SWITCH = 'switch';
    case CHECKBOX = 'checkbox';
    case CHECKBOX_GROUP = 'checkbox.group';
    case RADIO = 'radio';
    case FIELDSET = 'fieldset';
    case LEGEND = 'legend';

    // tables
    case TABLE = 'table';
    case THEAD = 'table.columns';
    case TH = 'table.column';
    case TBODY = 'table.rows';
    case TR = 'table.row';
    case TD = 'table.cell';

    // nav
    case NAVLIST_GROUP = 'navlist.group';
    case SIDEBAR_ITEM = 'sidebar.item';

    // modal
    case MODAL = 'modal';
    case MODAL_TRIGGER = 'modal.trigger';
    case MODAL_CLOSE = 'modal.close';

    // tooltip
    case TOOLTIP = 'tooltip';

}
