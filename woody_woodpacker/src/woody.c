/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   woody.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/15 12:02:55 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/16 12:39:38 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"
#include "../includes/elf.h"

void	test_ft_crypt()
{
	char test[255] = "abcdefghijklmnop\0";
	int size = ft_strlen(test) + 1;
	int key = 0;

	key = ft_cryptom(test, size);
	printf("key = %d | test = %s\n", key, test);
}

void	woody_start(void *ptr)
{
	Elf64_Ehdr *header;
    Elf64_Shdr *section_h;
	
	header = (void *)ptr;
	test_ft_crypt();

    /*printf("e_phoff = %llu, e_shoff = %llu\n", header->e_phoff, header->e_shoff);

    printf("sizeof: %lu\n", sizeof(*header));
    section_h = (void *)header + header->e_shoff;

    printf("section->sh_name: %u\n", section_h->sh_name);
    printf("section->sh_offset: %llu\n", section_h->sh_offset);
    printf("section->sh_link: %u\n", section_h->sh_link);
	for (uint16_t i = 0;i < header->e_phnum;i++)
	{
		// decrypt(section);
		section_h = (void *)section_h + sizeof(Elf64_Shdr);
		ft_cryptom(section_h)
		printf("section->sh_name: %u\n", section_h->sh_name);
		printf("section->sh_offset: %llu\n", section_h->sh_offset);
		printf("section->sh_link: %u\n", section_h->sh_link);
	}*/
}