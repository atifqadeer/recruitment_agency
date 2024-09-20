<?php

namespace Horsefly\Exports;

use Carbon\Carbon;
use Horsefly\Office;
use Horsefly\Sale;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClosedSalesEmailExport implements FromCollection ,WithHeadings
{
    protected $job_category;
    /**
     * @return \Illuminate\Support\Collection
     */
    function __construct($job_category) {
        $this->job_category = $job_category;
    }
    public function collection()
    {
        $query = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->select('offices.office_name', 'units.unit_name', 'sales.postcode',
                'units.contact_email','units.contact_name','units.contact_phone_number', 'sales.job_category')
            ->where(['sales.status' => 'disable', 'sales.is_on_hold' => '0']);

        if ($this->job_category == 'specialist'){
            $query->whereNotIn('sales.job_category', ['nurse','nonnurse']);
        }else{
            $query->where('sales.job_category', $this->job_category);
        }

        $result = $query->groupBy('units.unit_name')->get();

        return $result;
    }
    public function headings(): array
    {
        return [
            'Head Office',
            'Unit',
            'PostCode',
            'Email',
            'Contact Name',
            'Phone',
            'Job Title'
        ];
    }
}
