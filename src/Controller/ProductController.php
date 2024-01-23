<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    private $encoders;
    private $normalizers;
    private $serializer;

    public function __construct()
    {
        $this->encoders = [new JsonEncoder()];
        $this->normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
    }

    /**
     * @Route("/", name="app_product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findAll();
        $jsonContent = $serializer->serialize($products, 'json');

        return new JsonResponse($jsonContent, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("/new", name="app_product_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, ProductRepository $productRepository): JsonResponse
    {
        $jsonData = $request->getContent();

        try {
            // Deserializar JSON a objetos Product
            $products = $serializer->deserialize($jsonData, Product::class . '[]', 'json');

            // Validar los objetos
            foreach ($products as $product) {
                $errors = $validator->validate($product);
                if (count($errors) > 0) {
                    return new JsonResponse(['errors' => (string) $errors], JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            // Persistir y flush los nuevos registros
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($products as $product) {
                $entityManager->persist($product);
            }
            $entityManager->flush();

            return new JsonResponse(['message' => 'Registros creados exitosamente'], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/{id}", name="app_product_show", methods={"GET"})
     */
    public function show(Product $product): JsonResponse
    {
        return new JsonResponse();
    }

    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Product $product, ProductRepository $productRepository): JsonResponse
    {
        return new JsonResponse();
    }

    /**
     * @Route("/{id}", name="app_product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product, ProductRepository $productRepository): JsonResponse
    {
        return new JsonResponse();
    }
}
