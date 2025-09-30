<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatasetArrayImport implements ToCollection, WithHeadingRow
{
    /** @var \Illuminate\Support\Collection<int, array> */
    public Collection $rows;

    public function collection(Collection $rows): void
    {
        $this->rows = $rows->map(fn (Collection $row) => $row->toArray());
    }
}
