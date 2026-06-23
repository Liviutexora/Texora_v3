<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLayout;
use App\Helpers\ThemeHelper;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    /**
     * Preview email template layout
     */
    public function previewLayout(EmailTemplateLayout $layout)
    {
        $html = $layout->body;
        $html = ThemeHelper::getDynamicHtmlLayout($html);
        return view('filament.email-layouts.preview', ['html' => $html]);
    }

    /**
     * Preview email template
     */
    public function previewTemplate(EmailTemplate $template)
    {
        $layout = EmailTemplateLayout::where('is_active', true)->first();
        $layoutHtml = $layout ? $layout->body : "";
        $html = str_replace('{{BODY}}', $template->body, $layoutHtml);
        $html = ThemeHelper::getDynamicHtmlLayout($html);
        return view('filament.email-layouts.preview', ['html' => $html]);
    }
}

