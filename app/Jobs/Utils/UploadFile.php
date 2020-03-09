<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Jobs\Utils;

use App\Factory\NotificationFactory;
use App\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Intervention\Image\ImageManager;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const IMAGE = 1;
    const DOCUMENT = 2;

    const PROPERTIES = [
        self::IMAGE => [
            'path' => 'public/images',
        ],
        self::DOCUMENT => [
            'path' => 'public/documents',
        ]
    ];

    protected $file;
    protected $user;
    protected $account;
    protected $type;

    public $entity;

    public function __construct($file, $type, $user, $account, $entity, $disk = 'public')
    {
        $this->file = $file;
        $this->type = $type;
        $this->user = $user;
        $this->account = $account;
        $this->entity = $entity;
        $this->disk = $disk ?? config('filesystems.default');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): ?File
    {
        $instance = Storage::disk($this->disk)
                           ->putFileAs(self::PROPERTIES[$this->type]['path'], $this->file, $this->file->hashName());

        if (in_array($this->file->extension(), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'psd'])) {
            $image_size = getimagesize($this->file);

            $width = $image_size[0];
            $height = $image_size[1];
        }

        $document = new File();
        $document->user_id = $this->user->id;
        $document->account_id = $this->account->id;
        $document->file_path = $instance;
        $document->name = $this->file->getClientOriginalName();
        $document->type = $this->file->extension();
        $document->disk = $this->disk;
        $document->hash = $this->file->hashName();
        $document->size = $this->file->getSize();
        $document->width = isset($width) ?? null;
        $document->height = isset($height) ?? null;

// $preview_path = $this->encodePrimaryKey($this->company->id);
// $document->preview = $this->generatePreview($preview_path);

        $this->entity->documents()->save($document);

        $notification = NotificationFactory::create($this->account->id, $this->user->id);
        $notification->type = 'App\Notifications\AttachmentCreated';
        $notification->data = json_encode([
            'id' => $document->id,
            'message' => 'A new file has been uploaded',
            'filename' => $document->name
        ]);
        $notification->save();

        return $document;
    }

    private function generatePreview($preview_path): string
    {
        $extension = $this->file->getClientOriginalExtension();

        if (empty(File::$types[$extension]) && !empty(File::$extraExtensions[$extension])) {
            $documentType = File::$extraExtensions[$extension];
        } else {
            $documentType = $extension;
        }

        if (empty(File::$types[$documentType])) {
            return 'Unsupported file type';
        }

        $preview = '';

        if (in_array($this->file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'psd'])) {
            $makePreview = false;
            $imageSize = getimagesize($this->file);
            $width = $imageSize[0];
            $height = $imageSize[1];
            $imgManagerConfig = [];
            if (in_array($this->file->getClientOriginalExtension(), ['gif', 'bmp', 'tiff', 'psd'])) {
                // Needs to be converted
                $makePreview = true;
            } elseif ($width > File::DOCUMENT_PREVIEW_SIZE || $height > File::DOCUMENT_PREVIEW_SIZE) {
                $makePreview = true;
            }

            if (in_array($documentType, ['bmp', 'tiff', 'psd'])) {
                if (!class_exists('Imagick')) {
                    // Cant't read this
                    $makePreview = false;
                } else {
                    $imgManagerConfig['driver'] = 'imagick';
                }
            }

            if ($makePreview) {
                // We haven't created a preview yet
                $imgManager = new ImageManager($imgManagerConfig);

                $img = $imgManager->make($preview_path);

                if ($width <= File::DOCUMENT_PREVIEW_SIZE && $height <= File::DOCUMENT_PREVIEW_SIZE) {
                    $previewWidth = $width;
                    $previewHeight = $height;
                } elseif ($width > $height) {
                    $previewWidth = File::DOCUMENT_PREVIEW_SIZE;
                    $previewHeight = $height * File::DOCUMENT_PREVIEW_SIZE / $width;
                } else {
                    $previewHeight = File::DOCUMENT_PREVIEW_SIZE;
                    $previewWidth = $width * DOCUMENT_PREVIEW_SIZE / $height;
                }

                $img->resize($previewWidth, $previewHeight);

                $previewContent = (string)$img->encode($this->file->getClientOriginalExtension());

                Storage::put($preview_path, $previewContent);

                $preview = $preview_path;
            }
        }

        return $preview;
    }
}
