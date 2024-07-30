<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogImageRequest;
use App\Http\Requests\Blog\BlogRequest;
use App\Models\Blog;
use App\Models\Blog\Comment;
use App\Models\Blog\Tags;
use App\Models\BlogImages;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    public function allBlogs()
    {
        try {
            $blogs = Blog::with('images', 'tags', 'comments')->get();
            if ($blogs) {
                return ResponseHelper::success(message: 'All blogs', data: $blogs, statusCode: 201);
            }
            return ResponseHelper::error(message: 'Unable to retrieve all blogs! Please try again.', statusCode: 400);
        }
        catch (Exception $e) {
            Log::error('Unable to retrieve all blogs : ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to retrieve all blogs! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function createBlog(BlogRequest $request)
    {
        try {
            $user = Auth::user();

            // Check if the authenticated user is an admin
            if (!$user || !$user->is_admin) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            $blog = Blog::create([
                'author_name' => $user->full_name,
                'message' => $request->message,
                'title' => $request->title,
            ]);

            return ResponseHelper::success(message: 'Blog Created successfully!', data: $blog, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to create blogs: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to Create blogs! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function getBlogById($id)
    {
        try {
            $blog = Blog::find($id);

            return ResponseHelper::success(message: 'Single blog retrieved successfully!', data: $blog, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to retrieve blogs: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to retrieve blogs! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function uploadImages($id, BlogImageRequest $request)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return ResponseHelper::error(message: 'Blog not found.', statusCode: 404);
            }

            $img = $request->image;
            $ext = $img->getClientOriginalName();
            $imageName = time().'.'.$ext;
            $ImagePath = 'blogImages/' . $imageName;
            $img->move(public_path()."/blogImages/", $imageName);

            $image = new BlogImages;
            $image->blog_id = $blog->id;
            $image->image_path = $ImagePath;
            $image->save();

            if ($image) {
                return ResponseHelper::success(message: 'Image uploaded successfully!', data: $image, statusCode: 200);
            }
            return ResponseHelper::error(message: 'Unable to upload image! Please try again.', statusCode: 400);


        } catch (Exception $e) {
            Log::error('Unable to retrieve blogs: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to retrieve blogs! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function createTag($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:tags,name'
        ]);

        try {
            $user = Auth::user();

            // Check if the authenticated user is an admin
            if (!$user || !$user->is_admin) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            $blog = Blog::find($id);

            $tag = new Tags;
            $tag->blog_id = $blog->id;
            $tag->name = $request->name;
            $tag->save();

            return ResponseHelper::success(message: 'Tag created successfully!', data: $tag, statusCode: 201);


        } catch (Exception $e) {
            Log::error('Unable to retrieve blogs: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to retrieve blogs! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function getAllTags()
    {
        try {
            $tags = Tags::all();

            return ResponseHelper::success(message: 'All tags retrieved successfully!', data: $tags, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to retrieve tags: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to retrieve tags! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function updateTag($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:tags,name,' . $id
        ]);

        try {
            $tag = Tags::find($id);

            if (!$tag) {
                return ResponseHelper::error(message: 'Tag not found.', statusCode: 404);
            }

            $tag->name = $request->name;
            $tag->save();

            return ResponseHelper::success(message: 'Tag updated successfully!', data: $tag, statusCode: 200);

        } catch (Exception $e) {
            Log::error('Unable to update tag: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to update tag! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function deleteTag($id)
    {
        try {
            $tag = Tags::find($id);

            if (!$tag) {
                return ResponseHelper::error(message: 'Tag not found.', statusCode: 404);
            }

            $tag->delete();

            return ResponseHelper::success(message: 'Tag deleted successfully!', statusCode: 200);

        } catch (Exception $e) {
            Log::error('Unable to delete tag: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to delete tag! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function addComment($id, Request $request)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            $blog = Blog::find($id);
            if (!$blog) {
                return ResponseHelper::error(message: 'Blog not found.', statusCode: 404);
            }

            $comment = Comment::create([
                'blog_id' => $blog->id,
                'user_id' => $user->id,
                'comment' => $request->comment,
            ]);

            return ResponseHelper::success(message: 'Comment added successfully!', data: $comment, statusCode: 201);
        } catch (Exception $e) {
            Log::error('Unable to add comment: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to add comment! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }
    public function updateComment($id, Request $request)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            $comment = Comment::find($id);
            if (!$comment) {
                return ResponseHelper::error(message: 'Comment not found.', statusCode: 404);
            }

            // Check if the user is the owner of the comment or an admin
            if ($comment->user_id !== $user->id) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            $comment->comment = $request->comment;
            $comment->save();

            return ResponseHelper::success(message: 'Comment updated successfully!', data: $comment, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to update comment: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to update comment! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

    public function deleteComment($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            $comment = Comment::find($id);
            if (!$comment) {
                return ResponseHelper::error(message: 'Comment not found.', statusCode: 404);
            }

            // Check if the user is the owner of the comment or an admin
            if ($comment->user_id !== $user->id && !$user->is_admin) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            $comment->delete();

            return ResponseHelper::success(message: 'Comment deleted successfully!', statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to delete comment: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to delete comment! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }


}
