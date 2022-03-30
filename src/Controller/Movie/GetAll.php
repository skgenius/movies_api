<?php

namespace App\Controller\Movie;

use App\BaseModel\Result;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/api/movie/all")
 */
class GetAll extends AbstractController {
 
    public function __construct(
        MovieRepository $movieRepository
    ) {
        $this->movieRepository = $movieRepository;
    }

    public function __invoke( 
        Request $request
    ): JsonResponse  {
        $res = new Result();
        try {
            /*Default 
            $this->movieRepository->findBy(array(),array("share_email_count"=>"DESC"))
            */
            $res->success($this->movieRepository->findAll());
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, 200);
    }


}