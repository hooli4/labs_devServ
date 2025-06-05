<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use ZipArchive;
use Intervention\Image\Laravel\Facades\Image;


class FileController extends Controller
{
    public function upload(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:' . env('MAX_FILE_SIZE'), // Максимальный размер файла из переменной среды в КБайт
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $image = Image::read($file);

        $uuid = Str::uuid();

        $imageName = time().'-'.$uuid.'-'.$file->getClientOriginalName();

        if (!file_exists(public_path('images\\'.$user->id))) {
            mkdir(public_path('images\\'.$user->id));
        }

        $destinationPath = public_path('images\\'.$user->id.'\\');
        $image->save($destinationPath.$imageName);

        if (!file_exists(public_path('images\\'.$user->id.'\\thumbnail\\'))) {
            mkdir(public_path('images\\'.$user->id.'\\thumbnail\\'));
        }
        
        $destinationPathThumbnail = public_path('images\\'.$user->id.'\\thumbnail\\');
        $image->resize(128, 128);
        $image->save($destinationPathThumbnail.$imageName);

        $fileRecord = File::create([
            'name' => $imageName,
            'description' => $request->input('description'),
            'format' => $extension,
            'size' => $file->getSize(),
            'file_path' => $destinationPath,
        ]);

        return response()->json(['file' => $fileRecord], 201);
    }

    public function delete($id)
    {
        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'Unable to find file'], 404);
        }

        $user = Auth::user();

        $parts = explode('\\', $file->file_path);

        $userIdFromFilePath = $parts[count($parts) - 2];

        if ($user->id != $userIdFromFilePath) {
            return response()->json(['message' => 'You can not delete this file'], 403);
        }

        if ($user->avatar == $id) {
            $user->avatar = null;
            $user->save();
        }

        unlink($file->file_path.$file->name);
        unlink($file->file_path.'thumbnail\\'.$file->name);
        $file->delete();

        return response()->json(['message' => 'File deleted successfully'], 200);
    }

    public function download($id)
    {
        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'Unable to find file'], 404);
        }

        $user = Auth::user();

        $parts = explode('\\', $file->file_path);

        $userIdFromFilePath = $parts[count($parts) - 2];

        if ($user->id != $userIdFromFilePath) {
            return response()->json(['message' => 'You can not download this file'], 403);
        }

        return response()->download($file->file_path.$file->name);
    }

    public function setAvatar($id) {
        $user = Auth::user();

        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'Unable to find file'], 404);
        }

        $user = Auth::user();

        $parts = explode('\\', $file->file_path);

        $userIdFromFilePath = $parts[count($parts) - 2];

        if ($user->id != $userIdFromFilePath) {
            return response()->json(['message' => 'You can not set this file to avatar'], 403);
        }

        $user->avatar = $file->id;
        $user->save();

        return response()->json(['message' => 'Avatar successfully changed', 'avatar' => $user->avatar]);
    }

    public function getArchive() {
        $zip = new ZipArchive();
        $zipFileName = 'photos.zip';
        $zip->open(public_path($zipFileName), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = File::all();
        $data = [
            ['user_id', 'username', 'photo_created_at', '$filename_in_archive', 'path_on_server'],
        ];

        foreach ($files as $file) {

            $parts = explode('\\', $file->file_path);

            $userIdFromFilePath = $parts[count($parts) - 2];

            $user = User::find($userIdFromFilePath);

            $sub_data = [];

            if (!$user) {
                $zip->addFile($file->file_path.$file->name, 'unknown_'.$file->id.'.'.$file->format);
                $sub_data[] = 'unknown';
                $sub_data[] = 'unknown';
                $sub_data[] = $file->created_at;
                $sub_data[] = 'unknown_'.$file->id;
                $sub_data[] = $file->file_path.$file->name;
            }
            else {
                $zip->addFile($file->file_path.$file->name, $user->name.'_'.$file->id.'.'.$file->format);
                $sub_data[] = $user->id;
                $sub_data[] = $user->name;
                $sub_data[] = $file->created_at;
                $sub_data[] = $user->name.'_'.$file->id;
                $sub_data[] = $file->file_path.$file->name;
            }

            $data[] = $sub_data;
        }

        $users = User::whereNotNull('avatar')->get();
        foreach ($users as $user) {
            $file = File::find($user->avatar);
            $zip->addFile($file->file_path.'thumbnail\\'.$file->name, $user->name.'_'.$file->id.'_avatar.'.$file->format);
        }

        $export = new class($data) implements FromArray {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }
        };

        $filePath = public_path('report.xlsx');

        $excelFileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        file_put_contents($filePath, $excelFileContent);

        $zip->addFile(public_path('report.xlsx'), 'report.xlsx');

        $zip->close();

        return response()->download(public_path($zipFileName));
    }
}
