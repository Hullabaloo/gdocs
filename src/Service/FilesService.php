<?php

namespace App\Service;

class FilesService
{
    /**
     * Sanitize filename
     * @param string $filename
     * @return string
     */
    public function sanitizeFileName(string $filename): string
    {
        if ($_ENV['SANITIZE_FILENAME'] == "yes") {
            $file = mb_ereg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '', $filename);
            return mb_ereg_replace('([\.]{2,})', '', $file);
        } else
            return $filename;
    }

    /**
     * Check if filename is correct
     * @param string $input_line
     * @return bool
     */
    public function checkPartnerFileName(string $input_line): bool
    {
        if (preg_match('/^[a-zA-Zа-яА-ЯЁё"\']*_[0-9]{1,2}.(0[1-9]|1[0-2]).[0-9]{4}$/u', $input_line)
            || preg_match('/^[a-zA-Zа-яА-ЯЁё"\']*_[0-9]{1,2}.(0[1-9]|1[0-2]).[0-9]{4}.csv/u', $input_line))
            return true;
        else
            return false;
    }

    /**
     * Get real partner name from an original filename
     * @param string $fileName
     * @return string
     */
    public function getPartnerNameFromOriginalFileName(string $fileName): string
    {
        preg_match('/^[a-zA-Zа-яА-ЯЁё"\']*/u', $fileName, $output_array);
        return $output_array[0] ?? '';
    }

}
