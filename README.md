# PHP Data interchange format writer (DIF)
This package implements a simple [data interchange format (DIF)](https://en.wikipedia.org/wiki/Data_Interchange_Format) writer. 

## Usage
To create a DIF file, columns with data types have to be specified and the data has to be passed
as array to the writer:

    (new DifWriter())
        ->columns([
            'Text'   => DifWriter::TYPE_STRING,
            'Number' => DifWriter::TYPE_NUMERIC,
        ])
        ->data([
            [
                'Text'   => 'hello',
                'Number' => 1,
            ],
            [
                'Text'   => 'this is me',
                'Number' => -3.5,
            ],
        ])
        ->writeTo($target)
        
Input/output encoding may be specified as well as the linebreak to use using the corresponding
setter functions of the writer class.

By default the column headers are output as first line. To disable column header output, simply
pass `false` as second argument to the `columns()` method:

    $writer->columns([
                'Text'   => DifWriter::TYPE_STRING,
                'Number' => DifWriter::TYPE_NUMERIC,
            ], false);