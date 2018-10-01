<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Controls\AjaxUpload;

use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use RuntimeException;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\Upload\UploadControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Http\FileUpload;

class AjaxUploadControl extends UploadControl
{
    public function handleUpload(Form $form, $values)
    {
        try {
            if (is_array($values['upload'])) {
                /** @var FileUpload $fileUpload */
                foreach ($values['upload'] as $fileUpload) {
                    $file = \vojtabiberle\MediaStorage\Bridges\Nette\Http\FileUpload::createFromNetteFileUpload($fileUpload);
                    $file = $this->manager->save($file);
                    $info = new \stdClass();
                    $info->fileUID = $file->getUID();

                    //TODO: Configurable size here?
                    if($file->isImage()) {
                        $link = $this->presenter->link(':MediaStorage:Images:', ['name' => $file, 'size' => 'size-grid-thumbnail']);
                        $fullLink = $this->presenter->link(':MediaStorage:Images:', ['name' => $file]);
                    } else {
                        $link = $this->presenter->link(':MediaStorage:Icons:', ['name' => $file->getIconName(), 'size' => 'size-grid-thumbnail']);
                        $fullLink = $this->presenter->link(':MediaStorage:Files:', ['name' => $file]);
                    }

                    $info->fileLink = $link;
                    $info->fileFullLink = $fullLink;
                    $info->fileName = $file->getName();
                    $info->fileIsImage = $file->isImage();
                    $info->fileDeleteLink = $this->presenter->link(':MediaStorage:Media:delete', [$file->getUID()]);

                    $this->presenter->sendResponse(new JsonResponse($info));
                }
            } else {
                $info = new \stdClass();
                $info->error = true;
                $info->message = 'No files was uploaded';
                $this->presenter->sendResponse(new JsonResponse($info));
            }
        } catch (RuntimeException $e) {
            $info = new \stdClass();
            $info->error = true;
            $info->message = $e->getMessage();
            $this->presenter->sendResponse(new JsonResponse($info));
        }
    }
}