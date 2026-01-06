<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Note;
use App\Models\Tenant\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PleController extends Controller
{
    protected $count = 0;
    private $date;
    private $type;
    private $header;
    private $ft = 0;
    private $bv = 0;
    private $nc = 0;
    private $nd = 0;
    private $total = 0;
    public function index()
    {

        return view('account::ple.generate');
    }

    function create_cuo($id, $idx)
    {
        $cuo = $id . str_pad($idx, 9, "0", STR_PAD_LEFT);
        return $cuo;
    }
    function dates($month)
    {
        $carbon = Carbon::parse($month);
        $start = $carbon->startOfMonth()->format('Y-m-d');
        $end = $carbon->endOfMonth()->format('Y-m-d');
        return [$start, $end];
    }
    function type_documents($type)
    {
        switch ($type) {
            case '01':
                $this->ft += 1;
                break;
            case '03':
                $this->bv += 1;
                break;
            case '07':
                $this->nc += 1;
                break;
            default:
                $this->nd += 1;
                break;
        }
    }
    function info($doc)
    {
        $type = $doc->document_type_id;
        $total = $doc->total;
        $this->type_documents($type);
        $factor = $doc->state_type_id == "11" ? 0 : ($type == "07" ? -1 : 1);

        if ($doc->currency_type_id != "PEN") {
            $exchange = floatval($doc->exchange_rate_sale  ?? 1);
            $total *= $exchange;
        }
        $this->total += $factor * $total;
    }
    public function generate(Request $request)
    {
        $month = $request->month;

        $type = $request->type;
        $this->type = $type;
        $this->date = $month;
        $model = $type == 'sale' ? Document::class : Purchase::class;
        $rows = [];
        $state_type_id = $type == "sale" ? ["05", "11"] : ["01"];
        $this->header = str_replace("-", "", $month) . "00";
        $model::whereIn('document_type_id', ["01", "03", "07", "08"])
            ->whereIn('state_type_id', $state_type_id)
            ->where(function ($query) use ($month, $model) {
                if ($model == Purchase::class) {
                    $query->where(function ($q) use ($month) {
                        $q->whereNull('sunat_date')
                            ->whereBetween('date_of_due', $this->dates($month));
                    })
                        ->orWhere(function ($q) use ($month) {
                            $q->whereNotNull('sunat_date')
                                ->whereBetween('sunat_date', $this->dates($month));
                        });
                } else {
                    $query->whereBetween('date_of_issue', $this->dates($month));
                }
            })
            ->chunk(50, function ($documents) use (&$rows, $model) {

                foreach ($documents as  $doc) {
                    $this->count += 1;
                    $this->info($doc);
                    switch ($model) {
                        case Document::class:
                            $rows[] = $this->v14_1($doc);
                            break;

                        default:
                            $rows[] = $this->c8_1($doc);
                            break;
                    }
                }
            });
        $content = implode("\n", $rows);
        $name = $this->create_name_file();

        return [
            "name" => $name,
            "content" => $content,
            "ft" => $this->ft,
            "bv" => $this->bv,
            "nc" => $this->nc,
            "nd" => $this->nd,
            "total" => $this->total,
        ];
    }

    function create_name_file()
    {
        //LE 20601201641 2022 12 00 080100 00 1 1 1 1
        $company = Company::first();
        $ruc = $company->number;
        $year_month = explode("-", $this->date);
        $year = $year_month[0];
        $month = $year_month[1];
        $book_type = $this->type == "sale" ? "140100" : "080100";
        $name = "LE" . $ruc . $year . $month . "00" . $book_type . "001111.txt";

        return $name;
    }

    function v14_1($doc)
    {
        $issue_date = $doc->date_of_issue->format("d/m/Y");
        $row = [$this->header];  //1
        $cuo = $this->create_cuo($doc->id, $this->count);
        $row[] = $cuo;  //2
        $row[] = "M0001";  //3
        $row[] = $issue_date; //4
        $row[] = $issue_date;  //5
        $row[] = $doc->document_type_id;  //6
        $row[] = $doc->series;  //7
        $row[] = $doc->number;  //8
        $row[] = "";  //9
        $row[] = $doc->customer->identity_document_type_id;  //10
        $row[] = $doc->customer->number;  //11
        $row[] = $doc->customer->name;  //12
        $row[] = "0.00";  //13
        $row[] = $doc->state_type_id == '11' ? 0.00 : ($row[5] == "07" ? "" . $doc->total_taxed : $doc->total_taxed); //14
        $row[] = $doc->state_type_id == '11' ? 0.00 : ($row[5] == "07" ? 0.00 : $doc->total_discount); //1s
        $row[] = $doc->state_type_id == '11' ? 0.00 : ($row[5] == "07" ? "" . $doc->total_taxes : $doc->total_taxes); //16
        $row[] = "0.00";  //17
        $row[] = $doc->state_type_id == '11' ? 0.00 : ($row[5] == "07" ? "" . $doc->total_exonerated : $doc->total_exonerated); //18    
        $row[] = "0.00";  //19
        $row[] = "0.00";  //20
        $row[] = "0.00";  //21
        $row[] = "0.00";  //22
        $row[] = "0.00";  //23
        $row[] = "0.00";  //24
        $row[] = $doc->state_type_id == '11' ? 0.00 : ($row[5] == "07" ? "" . $doc->total : $doc->total); //25
        $row[] = $doc->currency_type_id; //26
        $row[] = $doc->exchange_rate_sale; //27
        $row[] = ""; //28
        $row[] = ""; //29
        $row[] = ""; //30
        $row[] = ""; //31
        if ($row[5] == "07" || $row[5] == "08") {
            $note = Note::where("document_id", $doc->id)->first();
            if ($note) {
                $document = Purchase::find($note->affected_document_id);
                if ($document) {
                    $row[27] = $document->date_of_issue->format("d/m/Y"); //28
                    $row[28] = $document->document_type_id; //29
                    $row[29] = $document->series; //30
                    $row[30] = $document->number; //31
                }
            }
        }


        $row[] = ""; //32
        $row[] = ""; //33
        $row[] = "1"; //34
        $row[] = "1"; //35
        $row[] = ""; //36

        $text_row = join("|", $row);

        return $text_row;
    }
    function c8_1($doc)
    {
        $issue_date = $doc->date_of_issue->format("d/m/Y");
        $row = [$this->header];  //1
        $cuo = $this->create_cuo($doc->id, $this->count);
        $row[] = $cuo;  //2
        $row[] = "M0001";  //3
        $row[] = $issue_date; //4
        $row[] = $issue_date;  //5
        $row[] = $doc->document_type_id;  //6
        $row[] = $doc->series;  //7
        $row[] = 0; //8
        $row[] = $doc->number;  //9
        $row[] = "";  //10
        $row[] = $doc->supplier->identity_document_type_id;  //11
        $row[] = $doc->supplier->number;  //12
        $row[] = $doc->supplier->name;  //13
        $row[] = $row[5] == "07" ? "" . $doc->total_value : $doc->total_value; //14
        $row[] = $row[5] == "07" ? "" . $doc->total_taxes : $doc->total_taxes; //15
        $row[] = $row[5] == "07" ? "" . $doc->exonerated : $doc->exonerated; //16
        $row[] = "0.00";  //17
        $row[] = "0.00";  //18
        $row[] = "0.00";  //19
        $row[] = "0.00";  //20
        $row[] = "0.00";  //21
        $row[] = "0.00";  //22
        $row[] = "0.00";  //23
        $row[] = $row[5] == "07" ? "" . $doc->total : $doc->total; //24
        $row[] = $doc->currency_type_id; //25
        $row[] = $doc->exchange_rate_sale; //26
        $row[] = ""; //27
        $row[] = ""; //28
        $row[] = ""; //29
        $row[] = ""; //30
        $row[] = ""; //31
        if ($row[5] == "07" || $row[5] == "08") {
            $note = Note::where("document_id", $doc->id)->first();
            if ($note) {
                $document = Document::find($note->affected_document_id);
                if ($document) {
                    $row[27] = $document->date_of_issue->format("d/m/Y"); //28
                    $row[28] = $document->document_type_id; //29
                    $row[29] = $document->series; //30
                    $row[30] = $document->number; //31
                }
            }
        }


        $row[] = ""; //32
        $row[] = ""; //33
        $row[] = ""; //34
        $row[] = "1"; //35
        $row[] = ""; //36
        $row[] = ""; //37
        $row[] = ""; //38
        $row[] = ""; //39
        $row[] = ""; //40
        $row[] = "1"; //41
        $row[] = "6"; //42
        $row[] = ""; //43

        $text_row = join("|", $row);

        return $text_row;
    }
}
