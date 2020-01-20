<?php


	namespace MehrItDataInterchangeFormatTest\Unit\Cases;


	use MehrIt\DataInterchangeFormat\DifWriter;
	use RuntimeException;

	class DifWriterTest extends TestCase
	{
		protected function assertResult(array $lines, $result, string $linebreak = "\n") {

			if (is_resource($result)) {
				\Safe\rewind($result);

				$result = \Safe\stream_get_contents($result);
			}

			$this->assertSame(implode($linebreak, $lines) . $linebreak, $result);

		}

		public function testWriteToString() {

			$output = (new DifWriter())
				->columns([
					'Text' => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->data([
					[
						'Text' => 'hello',
						'Number' => 1,
					],
					[
						'Text' => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"hello"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWriteToString_noHeaderOutput() {

			$output = (new DifWriter())
				->columns([
					'Text' => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				], false)
				->data([
					[
						'Text' => 'hello',
						'Number' => 1,
					],
					[
						'Text' => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,2',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"hello"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWriteToString_decimals() {

			$output = (new DifWriter())
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->data([
					[
						'Text'   => 'hello',
						'Number' => 1.8,
					],
					[
						'Text'   => 'has a double quote " in text',
						'Number' => '1,9780',
					],
				])
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"hello"',
					'0,1.8',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,1.978',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWriteToString_nullValues() {

			$output = (new DifWriter())
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->data([
					[
						'Text'   => null,
						'Number' => 1.8,
					],
					[
						'Text'   => 'has a double quote " in text',
						'Number' => null,
					],
				])
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'""',
					'0,1.8',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,0',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWriteToString_dataFieldsMissing() {

			$output = (new DifWriter())
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->data([
					[
						'Number' => 1.8,
					],
					[
						'Text'   => 'has a double quote " in text',
					],
				])
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'""',
					'0,1.8',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,0',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWriteToString_windowsLinebreak() {

			$output = (new DifWriter())
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
						'Text'   => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->setLinebreak("\r\n")
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"hello"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$output,
				"\r\n"
			);

		}

		public function testWriteToString_noAsciiChar() {

			$output = (new DifWriter())
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->data([
					[
						'Text'   => 'this Ä and €',
						'Number' => 1,
					],
					[
						'Text'   => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"this ? and ?"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWriteToString_outputWindows1252() {

			$output = (new DifWriter())
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->data([
					[
						'Text'   => 'this ä and €',
						'Number' => 1,
					],
					[
						'Text'   => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->setOutputEncoding('Windows-1252')
				->writeToString();

			$this->assertResult(
				mb_convert_encoding([
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"this ä and €"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				], 'Windows-1252', \Safe\mb_internal_encoding()),
				$output
			);

		}

		public function testWriteToString_inputIso8859_15_outputUtf8() {

			$output = (new DifWriter())
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->data([
					[
						'Text'   => mb_convert_encoding('this ä and €', 'ISO-8859-15', \Safe\mb_internal_encoding()),
						'Number' => 1,
					],
					[
						'Text'   => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->setInputEncoding('ISO-8859-15')
				->setOutputEncoding('UTF-8')
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"this ä and €"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWriteToString_customGeneratorComment() {

			$output = (new DifWriter())
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
						'Text'   => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->setGeneratorComment('EXCEL')
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"EXCEL"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"hello"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testWrite_resource() {

			$res = fopen('php://memory', 'w');

			$writer = new DifWriter();

			$this->assertSame($writer, $writer
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
						'Text'   => 'has a double quote " in text',
						'Number' => -3,
					],
				])
				->write($res));

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"MEHR IT DIF WRITER"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"hello"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$res
			);

		}

		public function testWrite_file() {

			$filename = \Safe\tempnam(sys_get_temp_dir(), 'PhpUnitDataInterchangeFormatTest');

			try {

				$writer = new DifWriter();

				$this->assertSame($writer, $writer
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
							'Text'   => 'has a double quote " in text',
							'Number' => -3,
						],
					])
					->write($filename));

				$this->assertResult(
					[
						'TABLE',
						'0,1',
						'"MEHR IT DIF WRITER"',
						'VECTORS',
						'0,2',
						'""',
						'TUPLES',
						'0,3',
						'""',
						'DATA',
						'0,0',
						'""',
						'-1,0',
						'BOT',
						'1,0',
						'"Text"',
						'1,0',
						'"Number"',
						'-1,0',
						'BOT',
						'1,0',
						'"hello"',
						'0,1',
						'V',
						'-1,0',
						'BOT',
						'1,0',
						'"has a double quote "" in text"',
						'0,-3',
						'V',
						'-1,0',
						'EOD',
					],
					\Safe\file_get_contents($filename)
				);

			}
			finally {
				if (file_exists($filename))
					unlink($filename);
			}
		}

		public function testWrite_noData() {

			$writer = (new DifWriter());

			$writer
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				]);

			$this->expectException(RuntimeException::class);

			$writer->write(fopen('php://memory', 'w+'));
		}

		public function testWrite_noColumns() {

			$writer = (new DifWriter());

			$writer
				->data([
					[
						'Text'   => 'hello',
						'Number' => 1,
					],
					[
						'Text'   => 'has a double quote " in text',
						'Number' => -3,
					],
				]);

			$this->expectException(RuntimeException::class);

			$writer->write(fopen('php://memory', 'w+'));
		}

		public function testAddData() {

			$output = (new DifWriter())
				->columns([
					'Text'   => DifWriter::TYPE_STRING,
					'Number' => DifWriter::TYPE_NUMERIC,
				])
				->addData([
					'Text'   => 'hello',
					'Number' => 1,
				])
				->addData([
					'Text'   => 'has a double quote " in text',
					'Number' => -3,
				])
				->setGeneratorComment('EXCEL')
				->writeToString();

			$this->assertResult(
				[
					'TABLE',
					'0,1',
					'"EXCEL"',
					'VECTORS',
					'0,2',
					'""',
					'TUPLES',
					'0,3',
					'""',
					'DATA',
					'0,0',
					'""',
					'-1,0',
					'BOT',
					'1,0',
					'"Text"',
					'1,0',
					'"Number"',
					'-1,0',
					'BOT',
					'1,0',
					'"hello"',
					'0,1',
					'V',
					'-1,0',
					'BOT',
					'1,0',
					'"has a double quote "" in text"',
					'0,-3',
					'V',
					'-1,0',
					'EOD',
				],
				$output
			);

		}

		public function testSettersGetters() {

			$writer = new DifWriter();

			$columns = [
				'Text'   => DifWriter::TYPE_STRING,
				'Number' => DifWriter::TYPE_NUMERIC,
			];

			$data = [
				[
					'Text'   => 'hello',
					'Number' => 1,
				],
				[
					'Text'   => 'has a double quote " in text',
					'Number' => -3,
				],
			];


			$this->assertSame($writer, $writer->columns($columns));
			$this->assertSame($writer, $writer->data($data));
			$this->assertSame($writer, $writer->setGeneratorComment('my comment'));
			$this->assertSame($writer, $writer->setInputEncoding('ASCII'));
			$this->assertSame($writer, $writer->setOutputEncoding('UTF-8'));
			$this->assertSame($writer, $writer->setLinebreak("\r\n"));


			$this->assertSame($columns, $writer->getColumns());
			$this->assertSame($data, $writer->getData());
			$this->assertSame('my comment', $writer->getGeneratorComment());
			$this->assertSame('ASCII', $writer->getInputEncoding());
			$this->assertSame('UTF-8', $writer->getOutputEncoding());
			$this->assertSame("\r\n", $writer->getLinebreak());

		}
	}