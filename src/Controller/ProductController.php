<?php

namespace App\Controller;

use Exception;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;

/**
 * @Route("/api/product")
 * @OA\Tag(name="Products")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="app_product_index", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/Product"))
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example=401),
     *         @OA\Property(property="message", type="string", example="JWT Token not found")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Internal Server Error",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="error", type="string", example="Exception: ...")
     *     )
     * )
     */
    public function index(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        try {
            $products = $productRepository->findAll();
            $jsonContent = $serializer->serialize($products, 'json');

            return new JsonResponse($jsonContent, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/new", name="app_product_new", methods={"POST"})
     * @OA\RequestBody(@OA\JsonContent(type="array",@OA\Items(ref="#/components/schemas/ProductPersist"))))
     * @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="message", type="string", example="Products created successfully")
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="error", type="string", example="Check the product with the SKU: A")
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example=401),
     *         @OA\Property(property="message", type="string", example="JWT Token not found")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Internal Server Error",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="error", type="string", example="Exception: ...")
     *     )
     * )
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $jsonData = $request->getContent();
            $products = $serializer->deserialize($jsonData, Product::class . '[]', 'json', [DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,]);

            foreach ($products as $product) {
                $entityManager->persist($product);
            }
            $entityManager->flush();

            return new JsonResponse(['message' => 'Products created successfully'], JsonResponse::HTTP_CREATED);
        } catch (PartialDenormalizationException $e) {
            preg_match('/\[(\d+)\]/', $e->getErrors()[0]->getPath(), $coincidencias);
            $data = json_decode($jsonData, true);
            return new JsonResponse(['error' => 'Check the product with the SKU: ' . $data[(int)$coincidencias[1]]['sku']], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Exception: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/edit", name="app_product_edit", methods={"PUT"})
     * @OA\RequestBody(@OA\JsonContent(type="array",@OA\Items(ref="#/components/schemas/ProductPersist"))))
     * @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="message", type="string", example="Products created successfully")
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="error", type="string", example="Check the product with the SKU: A")
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example=401),
     *         @OA\Property(property="message", type="string", example="JWT Token not found")
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="error", type="string", example="The product with the SKU was not found: X")
     *     )
     * )
     * @OA\Response(
     *     response=500,
     *     description="Internal Server Error",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="error", type="string", example="Exception: ...")
     *     )
     * )
     */
    public function edit(Request $request, SerializerInterface $serializer, ProductRepository $productRepository): JsonResponse
    {
        try {
            $jsonData = $request->getContent();
            $products = $serializer->deserialize($jsonData, Product::class . '[]', 'json', [DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,]);

            foreach ($products as $product) {
                $myProduct = $productRepository->findOneBy(['sku' => $product->getSku()]);
                if ($myProduct == null) {
                    return new JsonResponse(['error' => 'The product with the SKU was not found: ' . $product->getSku()], JsonResponse::HTTP_NOT_FOUND);
                }

                $myProduct->setProductName($product->getProductName());
                $myProduct->setDescription($product->getDescription());

                $productRepository->add($myProduct);
            }

            $productRepository->flush();

            return new JsonResponse(['message' => 'Products successfully updated'], JsonResponse::HTTP_OK);
        } catch (PartialDenormalizationException $e) {
            preg_match('/\[(\d+)\]/', $e->getErrors()[0]->getPath(), $coincidencias);
            $data = json_decode($jsonData, true);
            return new JsonResponse(['error' => 'Check the product with the SKU: ' . $data[(int)$coincidencias[1]]['sku']], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Exception: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
