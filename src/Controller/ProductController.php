<?php

namespace App\Controller;


use App\Entity\Product;
use App\Exception\FormValidationFailedException;
use App\Form\CreateProductType;
use App\Form\EditProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function create(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(EditProductType::class, $product);
        $form->submit($request->request->all());

        if(!$form->isSubmitted() || !$form->isValid())
        {
            throw new FormValidationFailedException($form);
        }

        $this->productRepository->add($product);

        return new JsonResponse([
            'id' => $product->getId()
        ], Response::HTTP_CREATED);
    }

    public function edit(Request $request, int $id)
    {
        $product = $this->productRepository->find($id);

        if($product == null) {
            return new JsonResponse([
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(CreateProductType::class, $product);
        $form->submit($request->request->all(), false);

        if(!$form->isSubmitted() || !$form->isValid())
        {
            throw new FormValidationFailedException($form);
        }

        $this->productRepository->update($product);

        return new JsonResponse([
            'id' => $product->getId()
        ], Response::HTTP_CREATED);
    }

    public function retrieve(int $id)
    {
        $product = $this->productRepository->find($id);

        if($product == null) {
            return new JsonResponse([
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($product->toState());
    }

    public function index(Request $request)
    {
        $lastId = $request->query->get('last_id', 0);
        $products = $this->productRepository->findPaged($lastId, 10);
        $result = [];

        foreach($products as $product)
        {
            $result[] = $product->toState();
        }

        return new JsonResponse($result);
    }
}