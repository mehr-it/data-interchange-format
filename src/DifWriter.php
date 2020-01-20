<?php


	namespace MehrIt\DataInterchangeFormat;


	use InvalidArgumentException;
	use MehrIt\PhpDecimals\Decimals;
	use RuntimeException;

	class DifWriter
	{

		CONST TYPE_NUMERIC = '0';
		CONST TYPE_STRING = '1';


		/**
		 * @var string
		 */
		protected $linebreak = "\n";

		/**
		 * @var string|null
		 */
		protected $inputEncoding;

		/**
		 * @var string
		 */
		protected $outputEncoding = 'ASCII';

		/**
		 * @var string[]
		 */
		protected $columns = [];

		/**
		 * @var float[][]|int[][]|null[][]|string[][]
		 */
		protected $data = [];

		/**
		 * @var string
		 */
		protected $generatorComment = 'MEHR IT DIF WRITER';

		/**
		 * @var bool
		 */
		protected $outputHeaders = true;

		/**
		 * @var string
		 */
		protected $internalEncoding;

		/**
		 * @var string
		 */
		protected $quoteInputEncoded;

		/**
		 * Creates a new instance
		 */
		public function __construct() {
			$this->inputEncoding = $this->internalEncoding = mb_internal_encoding();

			// prepare input encoded quote
			$this->quoteInputEncoded = $this->toInputEncoding('"');
		}

		/**
		 * Gets the linebreak
		 * @return string The linebreak
		 */
		public function getLinebreak(): string {
			return $this->linebreak;
		}

		/**
		 * Sets the linebreak
		 * @param string $linebreak The linebreak
		 * @return DifWriter
		 */
		public function setLinebreak(string $linebreak): DifWriter {
			$this->linebreak = $linebreak;

			return $this;
		}

		/**
		 * Gets the input encoding
		 * @return string|null Gets the input encoding. If null, internal encoding will be used
		 */
		public function getInputEncoding(): ?string {
			return $this->inputEncoding;
		}

		/**
		 * Sets the input encoding
		 * @param string|null $inputEncoding The input encoding. If null, internal encoding will be used
		 * @return DifWriter
		 */
		public function setInputEncoding(?string $inputEncoding): DifWriter {
			$this->inputEncoding = $inputEncoding;

			return $this;
		}

		/**
		 * Gets the output encoding
		 * @return string The output encoding
		 */
		public function getOutputEncoding(): string {
			return $this->outputEncoding;
		}

		/**
		 * Sets the output encoding
		 * @param string $outputEncoding The output encoding
		 * @return DifWriter
		 */
		public function setOutputEncoding(string $outputEncoding): DifWriter {
			$this->outputEncoding = $outputEncoding;

			return $this;
		}

		/**
		 * Gets the columns
		 * @return string[] The columns. Column name as key. Data type as value.
		 */
		public function getColumns(): array {
			return $this->columns;
		}

		/**
		 * Sets the columns
		 * @param string[] $columns The columns. Column name as key. Data type as value. See TYPE_*-constants
		 * @param bool $outputHeaders True if to output the columns as headers row
		 * @return DifWriter
		 */
		public function columns(array $columns, bool $outputHeaders = true): DifWriter {

			foreach($columns as $field => $type) {
				if ($type !== self::TYPE_NUMERIC && $type !== self::TYPE_STRING)
					throw new InvalidArgumentException("Invalid data type \"{$type}\" for column \"{$field}\"");
			}

			$this->columns       = $columns;
			$this->outputHeaders = $outputHeaders;

			return $this;
		}

		/**
		 * Gets the data
		 * @return float[][]|int[][]|null[][]|string[][] The data
		 */
		public function getData(): array {
			return $this->data;
		}

		/**
		 * Sets the data
		 * @param float[][]|int[][]|null[][]|string[][] $data The data lines. Column names as key. Values as value.
		 * @return DifWriter
		 */
		public function data(array $data) {
			$this->data = $data;

			return $this;
		}

		/**
		 * Adds a new data row
		 * @param float[]|int[]|null[]|string[] $rowData The data line. Column names as key. Values as value.
		 * @return DifWriter
		 */
		public function addData(array $rowData): DifWriter {
			$this->data[] = $rowData;

			return $this;
		}

		/**
		 * Gets the generator comment
		 * @return string The generator comment
		 */
		public function getGeneratorComment(): string {
			return $this->generatorComment;
		}

		/**
		 * Sets the generator comment
		 * @param string $generatorComment The generator comment
		 * @return DifWriter
		 */
		public function setGeneratorComment(string $generatorComment): DifWriter {
			$this->generatorComment = $generatorComment;

			return $this;
		}



		/**
		 * Writes the data and returns it as string
		 * @return string The data string
		 * @throws \Safe\Exceptions\FilesystemException
		 * @throws \Safe\Exceptions\StreamException
		 */
		public function writeToString(): string {

			$memResource = \Safe\fopen('php://memory', 'w+');

			$this->write($memResource);

			\Safe\rewind($memResource);

			return \Safe\stream_get_contents($memResource);
		}


		/**
		 * Write the data to the given target
		 * @param string|resource $target The resource or an URI. If a string is passed, a new resource will be created using fopen()
		 * @return DifWriter This instance
		 * @throws \Safe\Exceptions\FilesystemException
		 */
		public function write($target): DifWriter {

			$columnCount = count($this->columns);
			if ($columnCount < 1)
				throw new RuntimeException('No columns specified for DIF file');

			$rowCount = count($this->data);
			if ($rowCount < 1)
				throw new RuntimeException('No data specified for DIF file');

			// add headers row to count
			if ($this->outputHeaders)
				++$rowCount;


			if (!is_resource($target)) {
				$target = \Safe\fopen($target, 'w');
				$closeTarget = true;
			}
			else {
				$closeTarget = false;
			}

			$this->writeChunk(
				$this->headerChunk('TABLE',1, $this->generatorComment),
				$target
			);
			$this->writeChunk(
				$this->headerChunk('VECTORS', $columnCount),
				$target
			);
			$this->writeChunk(
				$this->headerChunk('TUPLES', $rowCount),
				$target
			);
			$this->writeChunk(
				$this->headerChunk('DATA', 0),
				$target
			);

			// output column headers
			if ($this->outputHeaders) {
				$this->writeChunk(
					$this->dataChunk('-1', 'BOT'),
					$target
				);

				foreach($this->columns as $field => $type) {
					$this->writeChunk(
						$this->dataChunk(self::TYPE_STRING, $field),
						$target
					);
				}
			}

			foreach($this->data as $currRow) {
				$this->writeChunk(
					$this->dataChunk('-1', 'BOT'),
					$target
				);

				foreach($this->columns as $field => $type) {
					$this->writeChunk(
						$this->dataChunk($type, $currRow[$field] ?? null),
						$target
					);
				}
			}

			$this->writeChunk(
				$this->dataChunk('-1', 'EOD'),
				$target
			);


			if ($closeTarget)
				\Safe\fclose($target);

			return $this;
		}



		/**
		 * Writes the given chunk to the target
		 * @param array $lines The chunk lines
		 * @param resource $target The target
		 * @throws \Safe\Exceptions\FilesystemException
		 */
		protected function writeChunk(array $lines, $target) {

			$linebreak = $this->linebreak;

			// encode for output
			/** @var string[] $lines */
			$lines = mb_convert_encoding($lines, $this->outputEncoding, $this->inputEncoding);

			\Safe\fwrite($target, implode($linebreak, $lines) . $linebreak);
		}


		/**
		 * Creates a new header chunk
		 * @param string $header The header
		 * @param int $value The header value
		 * @param string $str The header string
		 * @return string[] The chunk lines
		 */
		protected function headerChunk(string $header, int $value, string $str = ''): array {
			return $this->toInputEncoding([
				$header,
				"0,{$value}",
				$this->quote($str),
			]);
		}

		/**
		 * Creates a new data chunk
		 * @param string $type The chunk type. "-1" for directives, "0" for numbers, "1" for strings
		 * @param string|int|float $value The value
		 * @return string[] The chunk lines
		 */
		protected function dataChunk($type, $value): array {

			switch ($type) {
				case '-1':
					// directive
					return $this->toInputEncoding([
						'-1,0',
						$value,
					]);

				case '0':
					// number

					if ($value === null || trim($value) === '')
						$value = '0';
					else
						$value = Decimals::parse($value);

					return $this->toInputEncoding([
						"0,{$value}",
						'V',
					]);

				case '1':
					// string
					return [
						$this->toInputEncoding('1,0'),
						$this->quote($value),
					];

				default:
					throw new InvalidArgumentException("Unknown data chunk type \"{$type}\"");
			}
		}


		/**
		 * Quotes the given string
		 * @param string|null $value The string
		 * @return string The quoted string
		 */
		protected function quote(?string $value): string {

			$quote = $this->quoteInputEncoded;

			if ($value === null)
				return "{$quote}{$quote}";

			$value = str_replace($quote, "{$quote}{$quote}", $value);

			return "{$quote}{$value}{$quote}";
		}

		/**
		 * Converts the given line(s) to the input encoding
		 * @param string|string[] $lines
		 * @return string|string[] The encoded lines
		 */
		protected function toInputEncoding($lines) {

			if ($this->inputEncoding === $this->internalEncoding)
				return $lines;

			return mb_convert_encoding($lines, $this->inputEncoding, mb_internal_encoding());
		}

	}