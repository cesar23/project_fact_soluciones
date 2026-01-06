<?php

namespace Modules\Survey\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SurveyExportAll implements  FromView, ShouldAutoSize
{
    use Exportable;
    public function all_responses($all_responses) {
        $this->all_responses = $all_responses;
        return $this;
    }
    public function all_questions($all_questions) {
        $this->all_questions = $all_questions;
        return $this;
    }
    public function sections($sections) {
        $this->sections = $sections;
        return $this;
    }
    
    public function numberParticipants($number_participants) {
        $this->number_participants = $number_participants;
        return $this;
    }
    public function title($title) {
        $this->title = $title;
        return $this;
    }
    public function company($company) {
        $this->company = $company;

        return $this;
    }


    public function view(): View {
        return view('survey::exports.excel_all', [
            'title'=> $this->title,
            'sections'=> $this->sections,
            'company' => $this->company,
            'all_questions' => $this->all_questions,
            'all_responses' => $this->all_responses,
            'number_participants' => $this->number_participants,
        ]);
    }
}
